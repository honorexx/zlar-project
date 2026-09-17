<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$data=input_json(); $action=$data['action']??'listar'; $user=require_auth(); $pdo=db();
$statuses=['aberto','em_atendimento','resolvido','cancelado']; $urgencias=['Baixa','Media','Alta'];

if($action==='criar') {
  if(!in_array($user['tipo'],['morador','prestador'],true)) json_response(['ok'=>false,'message'=>'Acesso negado.'],403);
  $tipo=text_field($data,'tipo',160); $descricao=text_field($data,'descricao',3000); $urgencia=(string)($data['urgencia']??'Media');
  if(!in_array($urgencia,$urgencias,true)) json_response(['ok'=>false,'message'=>'Urgência inválida.'],422);
  $stmt=$pdo->prepare('INSERT INTO chamados_suporte (usuario_id,perfil,tipo,urgencia,descricao) VALUES (?,?,?,?,?)');
  $stmt->execute([$user['id'],$user['tipo'],$tipo,$urgencia,$descricao]);
  json_response(['ok'=>true,'id'=>(int)$pdo->lastInsertId(),'message'=>'Chamado aberto.'],201);
}

if($action==='atualizar'||$action==='excluir') {
  require_auth('admin'); $id=(int)($data['id']??0); if($id<1) json_response(['ok'=>false,'message'=>'Chamado inválido.'],422);
  if($action==='excluir') { $pdo->prepare('DELETE FROM chamados_suporte WHERE id=?')->execute([$id]); json_response(['ok'=>true,'message'=>'Chamado excluído.']); }
  $status=(string)($data['status']??''); $urgencia=(string)($data['urgencia']??'');
  if(!in_array($status,$statuses,true)||!in_array($urgencia,$urgencias,true)) json_response(['ok'=>false,'message'=>'Status ou urgência inválidos.'],422);
  $tipo=text_field($data,'tipo',160); $descricao=text_field($data,'descricao',3000); $resposta=optional_text($data,'resposta',3000);
  $stmt=$pdo->prepare('UPDATE chamados_suporte SET tipo=?,urgencia=?,descricao=?,status=?,resposta=? WHERE id=?');
  $stmt->execute([$tipo,$urgencia,$descricao,$status,$resposta?:null,$id]);
  if($stmt->rowCount()===0) { $check=$pdo->prepare('SELECT id FROM chamados_suporte WHERE id=?');$check->execute([$id]);if(!$check->fetch())json_response(['ok'=>false,'message'=>'Chamado não encontrado.'],404); }
  json_response(['ok'=>true,'message'=>'Chamado atualizado.']);
}

$sql='SELECT c.id,c.perfil,u.nome AS usuario_nome,u.email AS usuario_email,c.tipo,c.urgencia,c.descricao,c.status,c.resposta,c.criado_em,c.atualizado_em FROM chamados_suporte c JOIN usuarios u ON u.id=c.usuario_id';
if($user['tipo']==='admin') { $stmt=$pdo->query($sql.' ORDER BY c.criado_em DESC'); }
else { $stmt=$pdo->prepare($sql.' WHERE c.usuario_id=? ORDER BY c.criado_em DESC');$stmt->execute([$user['id']]); }
$rows=array_map(function($r){$r['id']=(int)$r['id'];return $r;},$stmt->fetchAll());
json_response(['ok'=>true,'chamados'=>$rows]);
