<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$user = require_auth('morador');

$data = input_json();
$categoria = trim($data['categoria'] ?? '');
$dataDesejada = trim($data['data'] ?? '');
$endereco = trim($data['endereco'] ?? '');
$descricao = trim($data['descricao'] ?? '');

if (!$categoria || !$endereco || !$descricao) {
  json_response(['ok' => false, 'message' => 'Preencha categoria, endereco e descricao.'], 422);
}
if ($dataDesejada && $dataDesejada < date('Y-m-d')) {
  json_response(['ok' => false, 'message' => 'A data desejada nao pode ser anterior a hoje.'], 422);
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM moradores WHERE usuario_id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$moradorId = $stmt->fetchColumn() ?: null;

$stmt = $pdo->prepare('INSERT INTO solicitacoes (morador_id, categoria, data_desejada, endereco, descricao, status) VALUES (?, ?, ?, ?, ?, "aberta")');
$stmt->execute([$moradorId, $categoria, $dataDesejada ?: null, $endereco, $descricao]);

json_response(['ok' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Solicitacao criada.']);
?>
