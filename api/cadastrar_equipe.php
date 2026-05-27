<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_auth('admin');

$data = input_json();
$nome = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$cargo = trim($data['cargo'] ?? '');

if (!$nome || !$email || !$cargo) {
  json_response(['ok' => false, 'message' => 'Preencha os campos obrigatorios.'], 422);
}

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
