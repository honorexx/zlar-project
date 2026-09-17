<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$data=input_json();$action=$data['action']??'gerar';$tipo=(string)($data['tipo']??'');$identificador=mb_strtolower(trim((string)($data['email']??'')));
if(!in_array($tipo,['morador','prestador','admin'],true)||$identificador==='')json_response(['ok'=>false,'message'=>'Informe o identificador e o tipo de usuário.'],422);
if (getenv('APP_ENV') !== 'local') json_response(['ok'=>false,'message'=>'Recuperação indisponível: o envio de e-mail ainda não foi configurado. Contate o administrador.'],503);
$pdo=db();
if($tipo==='admin'){$stmt=$pdo->prepare('SELECT id FROM admin_acessos WHERE (email=? OR usuario=?) AND status="ativo"');$stmt->execute([$identificador,$identificador]);}
else{$stmt=$pdo->prepare('SELECT id FROM usuarios WHERE email=? AND tipo=? AND status="ativo"');$stmt->execute([$identificador,$tipo]);}
if(!$stmt->fetchColumn())json_response(['ok'=>false,'message'=>'Cadastro ativo não encontrado.'],404);
if($action==='gerar') {
  $codigo=(string)random_int(100000,999999);$hash=hash('sha256',$codigo);$expira=date('Y-m-d H:i:s',time()+1800);
  $pdo->prepare('UPDATE recuperacoes_senha SET usado=1 WHERE identificador=? AND tipo=? AND usado=0')->execute([$identificador,$tipo]);
  $pdo->prepare('INSERT INTO recuperacoes_senha (identificador,tipo,codigo_hash,expira_em) VALUES (?,?,?,?)')->execute([$identificador,$tipo,$hash,$expira]);
  $response=['ok'=>true,'message'=>'Código gerado. No ambiente local ele é exibido abaixo.','expira_em'=>$expira];
  $response['codigo']=$codigo;
  json_response($response,201);
}
if($action!=='redefinir')json_response(['ok'=>false,'message'=>'Ação inválida.'],422);
$codigo=trim((string)($data['codigo']??''));$senha=(string)($data['senha']??'');$confirmacao=(string)($data['confirmar_senha']??'');
if(!preg_match('/^\d{6}$/',$codigo))json_response(['ok'=>false,'message'=>'Código inválido.'],422);
if($tipo==='admin') { if(strlen($senha)<6)json_response(['ok'=>false,'message'=>'O código deve ter ao menos 6 caracteres.'],422); }
else { require_once __DIR__.'/validators.php';ensure_password($senha,$confirmacao); }
if($senha!==$confirmacao)json_response(['ok'=>false,'message'=>'A confirmação não confere.'],422);
try { transaction(function(PDO $pdo) use($tipo,$identificador,$codigo,$senha) {
  $stmt=$pdo->prepare('SELECT id FROM recuperacoes_senha WHERE identificador=? AND tipo=? AND codigo_hash=? AND usado=0 AND expira_em>=NOW() ORDER BY id DESC LIMIT 1 FOR UPDATE');$stmt->execute([$identificador,$tipo,hash('sha256',$codigo)]);$id=$stmt->fetchColumn();if(!$id)throw new DomainException('codigo');
  if($tipo==='admin')$pdo->prepare('UPDATE admin_acessos SET codigo_hash=? WHERE email=? OR usuario=?')->execute([password_hash($senha,PASSWORD_DEFAULT),$identificador,$identificador]);
  else $pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE email=? AND tipo=?')->execute([password_hash($senha,PASSWORD_DEFAULT),$identificador,$tipo]);
  $pdo->prepare('UPDATE recuperacoes_senha SET usado=1 WHERE id=?')->execute([$id]);
}); } catch(DomainException $e){json_response(['ok'=>false,'message'=>'Código inválido ou expirado.'],422);}
json_response(['ok'=>true,'message'=>'Senha redefinida com sucesso.']);
