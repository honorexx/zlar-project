<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';

$data = input_json();
$user = require_auth();
if (!in_array($user['tipo'], ['morador', 'prestador'], true)) json_response(['ok' => false, 'message' => 'Perfil não autorizado.'], 403);
$action = $data['action'] ?? 'obter';
if ($action === 'obter') json_response(['ok' => true, 'user' => public_user($user)]);
if ($action !== 'atualizar') json_response(['ok' => false, 'message' => 'Ação inválida.'], 422);

$nome = text_field($data, 'nome', 160);
$telefone = text_field($data, 'telefone', 30);
ensure_phone($telefone);
$pdo = db();

try {
  if ($user['tipo'] === 'morador') {
    $email = mb_strtolower(text_field($data, 'email', 160));
    $endereco = text_field($data, 'endereco', 255);
    ensure_email($email);
    transaction(function(PDO $pdo) use ($user, $nome, $email, $telefone, $endereco) {
      $pdo->prepare('UPDATE usuarios SET nome=?, email=?, telefone=? WHERE id=? AND tipo="morador"')->execute([$nome, $email, $telefone, $user['id']]);
      $pdo->prepare('UPDATE moradores SET endereco=? WHERE usuario_id=?')->execute([$endereco, $user['id']]);
    });
  } else {
    $servico = text_field($data, 'servico', 120);
    $descricao = optional_text($data, 'descricao');
    if (!in_array($servico, ['Limpeza pesada','Limpeza diaria','Baba','Cuidador de idosos'], true)) json_response(['ok' => false, 'message' => 'Serviço inválido.'], 422);
    transaction(function(PDO $pdo) use ($user, $nome, $telefone, $servico, $descricao) {
      $pdo->prepare('UPDATE usuarios SET nome=?, telefone=? WHERE id=? AND tipo="prestador"')->execute([$nome, $telefone, $user['id']]);
      $pdo->prepare('UPDATE prestadores SET servico=?, descricao=? WHERE usuario_id=?')->execute([$servico, $descricao, $user['id']]);
    });
  }
} catch (PDOException $e) {
  if ($e->getCode() === '23000') json_response(['ok' => false, 'message' => 'Este e-mail já está em uso.'], 409);
  throw $e;
}
$fresh = refresh_session_user($user);
$_SESSION['zlar_user'] = $fresh;
json_response(['ok' => true, 'message' => 'Perfil atualizado.', 'user' => public_user($fresh)]);
