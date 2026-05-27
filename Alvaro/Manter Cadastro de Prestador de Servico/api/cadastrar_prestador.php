<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$data = input_json();
$nome = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$senha = trim($data['senha'] ?? '');
$confirmarSenha = trim($data['confirmar_senha'] ?? '');
$nascimento = trim($data['nascimento'] ?? '');
$servico = trim($data['servico'] ?? '');
$descricao = trim($data['descricao'] ?? '');

if (!$nome || !$email || !$telefone || !$senha || !$confirmarSenha || !$nascimento || !$servico) {
  json_response(['ok' => false, 'message' => 'Preencha os campos obrigatorios.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_response(['ok' => false, 'message' => 'Informe um e-mail valido.'], 422);
}
if (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
  json_response(['ok' => false, 'message' => 'Informe o telefone no formato (00) 00000-0000.'], 422);
}
if ($senha !== $confirmarSenha) {
  json_response(['ok' => false, 'message' => 'A confirmacao de senha nao confere.'], 422);
}
if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/\d/', $senha)) {
  json_response(['ok' => false, 'message' => 'A senha deve ter 8 caracteres, uma letra maiuscula e um numero.'], 422);
}
if ($nascimento > date('Y-m-d')) {
  json_response(['ok' => false, 'message' => 'A data de nascimento nao pode ser no futuro.'], 422);
}

$pdo = db();
try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare('INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status) VALUES ("prestador", ?, ?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
  $usuarioId = $pdo->lastInsertId();

  $stmt = $pdo->prepare('INSERT INTO prestadores (usuario_id, servico, descricao, nascimento, status_aprovacao) VALUES (?, ?, ?, ?, "em_analise")');
  $stmt->execute([$usuarioId, $servico, $descricao, $nascimento]);

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
} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe cadastro com este e-mail.'], 409);
  }
  json_response(['ok' => false, 'message' => 'Nao foi possivel cadastrar o prestador.'], 500);
}
?>
