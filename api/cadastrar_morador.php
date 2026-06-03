<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validators.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$data = input_json();
$nome = required_text($data, 'nome');
$email = required_text($data, 'email');
$telefone = required_text($data, 'telefone');
$cpf = required_text($data, 'cpf');
$senha = required_text($data, 'senha');
$endereco = required_text($data, 'endereco');
$nascimento = required_text($data, 'nascimento');
$confirmarSenha = required_text($data, 'confirmar_senha');

ensure_required([
  'nome' => $nome,
  'email' => $email,
  'telefone' => $telefone,
  'CPF' => $cpf,
  'senha' => $senha,
  'confirmar senha' => $confirmarSenha,
  'endereco' => $endereco,
  'data de nascimento' => $nascimento,
]);
ensure_email($email);
ensure_phone($telefone);
ensure_cpf($cpf);
ensure_password($senha, $confirmarSenha);
ensure_not_future_date($nascimento, 'A data de nascimento');

$pdo = db();
try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare('INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status) VALUES ("morador", ?, ?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
  $usuarioId = $pdo->lastInsertId();

  if (table_has_column($pdo, 'moradores', 'nascimento')) {
    $stmt = $pdo->prepare('INSERT INTO moradores (usuario_id, cpf, endereco, nascimento) VALUES (?, ?, ?, ?)');
    $stmt->execute([$usuarioId, $cpf, $endereco, $nascimento]);
  } else {
    $stmt = $pdo->prepare('INSERT INTO moradores (usuario_id, cpf, endereco) VALUES (?, ?, ?)');
    $stmt->execute([$usuarioId, $cpf, $endereco]);
  }

  $pdo->commit();
  $user = [
    'id' => $usuarioId,
    'tipo' => 'morador',
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
    'cpf' => $cpf,
    'endereco' => $endereco,
    'nascimento' => $nascimento
  ];
  $_SESSION['zlar_user'] = $user;
  json_response([
    'ok' => true,
    'id' => $usuarioId,
    'message' => 'Morador cadastrado com sucesso.',
    'user' => $user
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($e instanceof PDOException && $e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe cadastro com este e-mail ou CPF.'], 409);
  }
  json_response([
    'ok' => false,
    'message' => 'Nao foi possivel cadastrar o morador.',
    'debug' => $e->getMessage()
  ], 500);
}
?>
