<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';
$admin=require_auth('admin'); $data=input_json(); $action=$data['action']??'listar'; $tipo=(string)($data['tipo']??'');
if(!in_array($tipo,['morador','prestador'],true)) json_response(['ok'=>false,'message'=>'Tipo de usuário inválido.'],422);
$pdo=db();
if($action==='listar') {
  if($tipo==='morador') $sql='SELECT u.id,u.nome,u.email,u.telefone,u.status,m.cpf,m.endereco,m.nascimento FROM usuarios u JOIN moradores m ON m.usuario_id=u.id WHERE u.tipo="morador" ORDER BY u.nome';
  else $sql='SELECT u.id,u.nome,u.email,u.telefone,u.status,p.servico,p.descricao,p.nascimento,p.status_aprovacao,p.nota_media,p.total_avaliacoes FROM usuarios u JOIN prestadores p ON p.usuario_id=u.id WHERE u.tipo="prestador" ORDER BY u.nome';
  $rows=$pdo->query($sql)->fetchAll(); foreach($rows as &$r)$r['id']=(int)$r['id'];
  json_response(['ok'=>true,'tipo'=>$tipo,'total'=>count($rows),'usuarios'=>$rows]);
}
$id=(int)($data['id']??0); if($id<1) json_response(['ok'=>false,'message'=>'Usuário inválido.'],422);
if($action==='excluir') {
  try { $stmt=$pdo->prepare('DELETE FROM usuarios WHERE id=? AND tipo=?');$stmt->execute([$id,$tipo]); }
  catch(PDOException $e) { if($e->getCode()==='23000') json_response(['ok'=>false,'message'=>'Este usuário possui histórico e deve ser bloqueado, não excluído.'],409);throw $e; }
  if($stmt->rowCount()!==1) json_response(['ok'=>false,'message'=>'Usuário não encontrado.'],404);
  json_response(['ok'=>true,'message'=>'Usuário excluído.']);
}
if($action!=='atualizar') json_response(['ok'=>false,'message'=>'Ação inválida.'],422);
$nome=text_field($data,'nome',160);$email=mb_strtolower(text_field($data,'email',160));$telefone=text_field($data,'telefone',30);$status=(string)($data['status']??'');
ensure_email($email);ensure_phone($telefone);if(!in_array($status,['ativo','inativo','bloqueado'],true))json_response(['ok'=>false,'message'=>'Status inválido.'],422);
try {
  transaction(function(PDO $pdo) use($data,$tipo,$id,$nome,$email,$telefone,$status) {
    $finalStatus=$status;
    if($tipo==='morador') {
      $cpf=text_field($data,'cpf',20);$endereco=text_field($data,'endereco',255);ensure_cpf($cpf);
      $pdo->prepare('UPDATE moradores SET cpf=?,endereco=? WHERE usuario_id=?')->execute([$cpf,$endereco,$id]);
    } else {
      $servico=(string)($data['servico']??'');$descricao=optional_text($data,'descricao');$aprovacao=(string)($data['status_aprovacao']??'');
      if(!in_array($servico,['Limpeza pesada','Limpeza diaria','Baba','Cuidador de idosos'],true)||!in_array($aprovacao,['em_analise','aprovado','reprovado','bloqueado'],true))json_response(['ok'=>false,'message'=>'Serviço ou aprovação inválidos.'],422);
      if($aprovacao==='bloqueado')$finalStatus='bloqueado';
      $pdo->prepare('UPDATE prestadores SET servico=?,descricao=?,status_aprovacao=? WHERE usuario_id=?')->execute([$servico,$descricao,$aprovacao,$id]);
    }
    $stmt=$pdo->prepare('UPDATE usuarios SET nome=?,email=?,telefone=?,status=? WHERE id=? AND tipo=?');$stmt->execute([$nome,$email,$telefone,$finalStatus,$id,$tipo]);
  });
} catch(PDOException $e) { if($e->getCode()==='23000')json_response(['ok'=>false,'message'=>'E-mail ou CPF já cadastrado.'],409);throw $e; }
json_response(['ok'=>true,'message'=>'Usuário atualizado.']);
