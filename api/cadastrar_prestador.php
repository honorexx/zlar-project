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
$senha = required_text($data, 'senha');
$confirmarSenha = required_text($data, 'confirmar_senha');
$nascimento = required_text($data, 'nascimento');
$servico = required_text($data, 'servico');
$descricao = required_text($data, 'descricao');

ensure_required([
  'nome' => $nome,
  'email' => $email,
  'telefone' => $telefone,
  'senha' => $senha,
  'confirmar senha' => $confirmarSenha,
  'data de nascimento' => $nascimento,
  'servico' => $servico,
]);
ensure_email($email);
ensure_phone($telefone);
ensure_password($senha, $confirmarSenha);
ensure_not_future_date($nascimento, 'A data de nascimento');

$pdo = db();
try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare('INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status) VALUES ("prestador", ?, ?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
  $usuarioId = $pdo->lastInsertId();

  if (table_has_column($pdo, 'prestadores', 'nascimento')) {
    $stmt = $pdo->prepare('INSERT INTO prestadores (usuario_id, servico, descricao, nascimento, status_aprovacao) VALUES (?, ?, ?, ?, "em_analise")');
    $stmt->execute([$usuarioId, $servico, $descricao, $nascimento]);
  } else {
    $stmt = $pdo->prepare('INSERT INTO prestadores (usuario_id, servico, descricao, status_aprovacao) VALUES (?, ?, ?, "em_analise")');
    $stmt->execute([$usuarioId, $servico, $descricao]);
  }

  $pdo->commit();
  $user = [
    'id' => $usuarioId,
    'tipo' => 'prestador',
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
    'servico' => $servico,
    'descricao' => $descricao,
    'nascimento' => $nascimento
  ];
  $_SESSION['zlar_user'] = $user;
  json_response([
    'ok' => true,
    'id' => $usuarioId,
    'message' => 'Prestador cadastrado com sucesso.',
    'user' => $user
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($e instanceof PDOException && $e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe cadastro com este e-mail.'], 409);
  }
  json_response([
    'ok' => false,
    'message' => 'Nao foi possivel cadastrar o prestador.',
    'debug' => $e->getMessage()
  ], 500);
}
?>
