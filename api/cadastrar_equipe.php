<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';

require_auth('admin');

$data = input_json();
$nome = required_text($data, 'nome');
$email = required_text($data, 'email');
$cargo = required_text($data, 'cargo');

ensure_required([
  'nome' => $nome,
  'email' => $email,
  'cargo' => $cargo,
]);
ensure_email($email);

$pdo = db();
try {
  $stmt = $pdo->prepare('INSERT INTO equipe_suporte (nome, email, cargo, status) VALUES (?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $cargo]);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe membro da equipe com este e-mail.'], 409);
  }
  json_response(['ok' => false, 'message' => 'Nao foi possivel cadastrar o membro da equipe.'], 500);
}

json_response(['ok' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Membro da equipe cadastrado.']);
?>
