<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$data = input_json();
$nome = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$cpf = trim($data['cpf'] ?? '');
$senha = trim($data['senha'] ?? '');
$endereco = trim($data['endereco'] ?? '');
$nascimento = trim($data['nascimento'] ?? '');
$confirmarSenha = trim($data['confirmar_senha'] ?? '');

if (!$nome || !$email || !$telefone || !$cpf || !$senha || !$confirmarSenha || !$endereco || !$nascimento) {
  json_response(['ok' => false, 'message' => 'Preencha os campos obrigatorios.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_response(['ok' => false, 'message' => 'Informe um e-mail valido.'], 422);
}
if (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
  json_response(['ok' => false, 'message' => 'Informe o telefone no formato (00) 00000-0000.'], 422);
}
if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
  json_response(['ok' => false, 'message' => 'Informe o CPF no formato 000.000.000-00.'], 422);
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

  $stmt = $pdo->prepare('INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status) VALUES ("morador", ?, ?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
  $usuarioId = $pdo->lastInsertId();

  $stmt = $pdo->prepare('INSERT INTO moradores (usuario_id, cpf, endereco, nascimento) VALUES (?, ?, ?, ?)');
  $stmt->execute([$usuarioId, $cpf, $endereco, $nascimento]);

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
} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe cadastro com este e-mail ou CPF.'], 409);
  }
  json_response(['ok' => false, 'message' => 'Nao foi possivel cadastrar o morador.'], 500);
}
?>
