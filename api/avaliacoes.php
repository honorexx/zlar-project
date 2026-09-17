<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$data=input_json(); $action=$data['action']??'listar'; $user=require_auth(); $pdo=db();

if ($action==='criar') {
  if ($user['tipo']!=='morador') json_response(['ok'=>false,'message'=>'Somente o morador pode avaliar.'],403);
  $id=(int)($data['id']??0); $nota=(int)($data['nota']??0); $comentario=optional_text($data,'comentario');
  if ($nota<1||$nota>5) json_response(['ok'=>false,'message'=>'Escolha uma nota de 1 a 5.'],422);
  try { transaction(function(PDO $pdo) use($user,$id,$nota,$comentario) {
    $stmt=$pdo->prepare('SELECT s.morador_id,s.prestador_id FROM solicitacoes s JOIN moradores m ON m.id=s.morador_id WHERE s.id=? AND m.usuario_id=? AND s.status="pago" AND s.prestador_id IS NOT NULL FOR UPDATE');
    $stmt->execute([$id,$user['id']]); $s=$stmt->fetch(); if(!$s) throw new DomainException('estado');
    $pdo->prepare('INSERT INTO avaliacoes (solicitacao_id,morador_id,prestador_id,nota,comentario) VALUES (?,?,?,?,?)')->execute([$id,$s['morador_id'],$s['prestador_id'],$nota,$comentario?:null]);
    $pdo->prepare('UPDATE prestadores p SET nota_media=(SELECT AVG(a.nota) FROM avaliacoes a WHERE a.prestador_id=p.id), total_avaliacoes=(SELECT COUNT(*) FROM avaliacoes a WHERE a.prestador_id=p.id) WHERE p.id=?')->execute([$s['prestador_id']]);
  }); } catch(DomainException $e) { json_response(['ok'=>false,'message'=>'Apenas serviços pagos podem ser avaliados.'],409); }
  catch(PDOException $e) { if($e->getCode()==='23000') json_response(['ok'=>false,'message'=>'Esta solicitação já foi avaliada.'],409); throw $e; }
  json_response(['ok'=>true,'message'=>'Avaliação enviada.'],201);
}

$base='SELECT a.id,a.solicitacao_id AS solicitacaoId,a.nota,a.comentario,a.criado_em AS criadoEm,s.categoria AS servico,um.nome AS morador,up.nome AS prestador FROM avaliacoes a JOIN solicitacoes s ON s.id=a.solicitacao_id JOIN moradores m ON m.id=a.morador_id JOIN usuarios um ON um.id=m.usuario_id JOIN prestadores p ON p.id=a.prestador_id JOIN usuarios up ON up.id=p.usuario_id WHERE ';
if($user['tipo']==='morador') { $where='m.usuario_id=?'; }
elseif($user['tipo']==='prestador') { $where='p.usuario_id=?'; }
else json_response(['ok'=>false,'message'=>'Acesso negado.'],403);
$stmt=$pdo->prepare($base.$where.' ORDER BY a.criado_em DESC'); $stmt->execute([$user['id']]);
$rows=array_map(function($r){$r['id']=(int)$r['id'];$r['solicitacaoId']=(int)$r['solicitacaoId'];$r['nota']=(int)$r['nota'];return $r;},$stmt->fetchAll());
json_response(['ok'=>true,'avaliacoes'=>$rows]);
