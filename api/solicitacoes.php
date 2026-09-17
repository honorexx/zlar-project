<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';

$data = input_json();
$action = $data['action'] ?? 'listar';
$user = require_auth();
$pdo = db();

function request_rows(PDO $pdo, string $where, array $params): array {
  $sql = 'SELECT s.id, s.categoria, s.data_desejada AS data, s.endereco, s.descricao, s.status, s.criado_em,
                 um.nome AS morador,
                 up.nome AS prestador, up.email AS prestadorEmail, up.telefone AS prestadorTelefone,
                 p.servico AS prestadorServico, p.descricao AS prestadorDescricao,
                 pg.valor, pg.chave_pix AS pix, pg.observacao AS observacaoPagamento, pg.status AS pagamento,
                 CASE WHEN a.id IS NULL THEN NULL ELSE "enviada" END AS avaliacaoStatus
            FROM solicitacoes s
            JOIN moradores m ON m.id=s.morador_id JOIN usuarios um ON um.id=m.usuario_id
       LEFT JOIN prestadores p ON p.id=s.prestador_id LEFT JOIN usuarios up ON up.id=p.usuario_id
       LEFT JOIN pagamentos pg ON pg.solicitacao_id=s.id LEFT JOIN avaliacoes a ON a.solicitacao_id=s.id
           WHERE ' . $where . ' ORDER BY s.criado_em DESC';
  $stmt = $pdo->prepare($sql); $stmt->execute($params);
  return array_map(function($row) { $row['id']=(int)$row['id']; if ($row['valor'] !== null) $row['valor']=number_format((float)$row['valor'],2,',','.'); return $row; }, $stmt->fetchAll());
}

function resident_id(PDO $pdo, int $userId): int {
  $stmt=$pdo->prepare('SELECT id FROM moradores WHERE usuario_id=?'); $stmt->execute([$userId]);
  $id=$stmt->fetchColumn(); if (!$id) json_response(['ok'=>false,'message'=>'Perfil de morador não encontrado.'],404); return (int)$id;
}
function provider(PDO $pdo, int $userId): array {
  $stmt=$pdo->prepare('SELECT id,servico,status_aprovacao FROM prestadores WHERE usuario_id=?'); $stmt->execute([$userId]);
  $row=$stmt->fetch(); if (!$row) json_response(['ok'=>false,'message'=>'Perfil de prestador não encontrado.'],404); $row['id']=(int)$row['id']; return $row;
}

if ($action === 'listar') {
  if ($user['tipo'] === 'morador') {
    $rows=request_rows($pdo,'s.morador_id=?',[resident_id($pdo,$user['id'])]);
  } elseif ($user['tipo'] === 'prestador') {
    $user=require_approved_provider(); $p=provider($pdo,$user['id']);
    $rows=request_rows($pdo,'(s.status="aberta" AND s.prestador_id IS NULL AND s.categoria=?) OR s.prestador_id=?',[$p['servico'],$p['id']]);
  } else json_response(['ok'=>false,'message'=>'Área sem acesso às solicitações.'],403);
  json_response(['ok'=>true,'solicitacoes'=>$rows]);
}

$id=(int)($data['id']??0); if ($id<1) json_response(['ok'=>false,'message'=>'Solicitação inválida.'],422);
if ($user['tipo']==='prestador') {
  $user=require_approved_provider(); $p=provider($pdo,$user['id']);
  if ($action==='aceitar') {
    $stmt=$pdo->prepare('UPDATE solicitacoes SET prestador_id=?,status="aceita",aceito_em=NOW() WHERE id=? AND status="aberta" AND prestador_id IS NULL AND categoria=?');
    $stmt->execute([$p['id'],$id,$p['servico']]);
    if ($stmt->rowCount()!==1) json_response(['ok'=>false,'message'=>'Esta solicitação não está mais disponível ou não corresponde ao seu serviço.'],409);
  } elseif ($action==='iniciar') {
    $stmt=$pdo->prepare('UPDATE solicitacoes SET status="em_andamento",iniciado_em=NOW() WHERE id=? AND prestador_id=? AND status="aceita"'); $stmt->execute([$id,$p['id']]);
    if ($stmt->rowCount()!==1) json_response(['ok'=>false,'message'=>'Somente um serviço aceito por você pode ser iniciado.'],409);
  } elseif ($action==='concluir') {
    $stmt=$pdo->prepare('UPDATE solicitacoes SET status="concluida",concluido_em=NOW() WHERE id=? AND prestador_id=? AND status="em_andamento"'); $stmt->execute([$id,$p['id']]);
    if ($stmt->rowCount()!==1) json_response(['ok'=>false,'message'=>'Somente um serviço em andamento pode ser concluído.'],409);
  } elseif ($action==='solicitar_pagamento') {
    $valor=payment_amount($data['valor']??''); $pix=text_field($data,'pix',255); $obs=optional_text($data,'observacao');
    try { transaction(function(PDO $pdo) use($id,$p,$valor,$pix,$obs) {
      $stmt=$pdo->prepare('UPDATE solicitacoes SET status="pagamento_solicitado" WHERE id=? AND prestador_id=? AND status="concluida"'); $stmt->execute([$id,$p['id']]);
      if ($stmt->rowCount()!==1) throw new DomainException('transicao');
      $pdo->prepare('INSERT INTO pagamentos (solicitacao_id,valor,chave_pix,observacao) VALUES (?,?,?,?)')->execute([$id,$valor,$pix,$obs?:null]);
    }); } catch(DomainException $e) { json_response(['ok'=>false,'message'=>'O pagamento só pode ser solicitado após a conclusão.'],409); }
  } else json_response(['ok'=>false,'message'=>'Ação inválida para prestador.'],422);
} elseif ($user['tipo']==='morador' && $action==='pagar') {
  $m=resident_id($pdo,$user['id']);
  try { transaction(function(PDO $pdo) use($id,$m) {
    $stmt=$pdo->prepare('UPDATE solicitacoes SET status="pago" WHERE id=? AND morador_id=? AND status="pagamento_solicitado"'); $stmt->execute([$id,$m]);
    if ($stmt->rowCount()!==1) throw new DomainException('transicao');
    $stmt=$pdo->prepare('UPDATE pagamentos SET status="pago",pago_em=NOW() WHERE solicitacao_id=? AND status="pendente"'); $stmt->execute([$id]);
    if ($stmt->rowCount()!==1) throw new DomainException('pagamento');
  }); } catch(DomainException $e) { json_response(['ok'=>false,'message'=>'Este pagamento não está pendente para você.'],409); }
} else json_response(['ok'=>false,'message'=>'Ação não autorizada.'],403);
json_response(['ok'=>true,'message'=>'Solicitação atualizada.']);
