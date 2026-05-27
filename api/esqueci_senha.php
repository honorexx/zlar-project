<?php
require_once __DIR__ . '/db.php';

$data = input_json();
$email = trim($data['email'] ?? '');
$tipo = trim($data['tipo'] ?? '');

if (!$email || !$tipo) {
  json_response(['ok' => false, 'message' => 'Informe e-mail e tipo de usuario.'], 422);
}

$codigo = (string) random_int(100000, 999999);
$expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO recuperacoes_senha (email, tipo, codigo, expira_em) VALUES (?, ?, ?, ?)');
$stmt->execute([$email, $tipo, $codigo, $expira]);

json_response([
  'ok' => true,
  'message' => 'Codigo de recuperacao gerado.',
  'codigo' => $codigo,
  'expira_em' => $expira
]);
?>
