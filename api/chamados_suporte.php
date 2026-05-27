<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$data = input_json();
$action = $data['action'] ?? 'listar';
$pdo = db();

if ($action === 'criar') {
  $user = require_auth($data['perfil'] ?? null);
  $stmt = $pdo->prepare('INSERT INTO chamados_suporte (perfil, usuario_nome, usuario_email, tipo, urgencia, descricao) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt->execute([
    $data['perfil'] ?? 'morador',
    $user['nome'] ?? ($data['usuario'] ?? 'Usuario'),
    $user['email'] ?? ($data['email'] ?? ''),
    $data['tipo'] ?? '',
    $data['urgencia'] ?? 'Media',
    $data['descricao'] ?? ''
  ]);
  json_response(['ok' => true, 'id' => $pdo->lastInsertId()]);
}

if ($action === 'atualizar') {
  require_auth('admin');
  $stmt = $pdo->prepare('UPDATE chamados_suporte SET status = ?, resposta = ?, atualizado_em = NOW() WHERE id = ?');
  $stmt->execute([$data['status'] ?? 'em_atendimento', $data['resposta'] ?? '', $data['id'] ?? 0]);
  json_response(['ok' => true]);
}

if ($action === 'excluir') {
  require_auth('admin');
  $stmt = $pdo->prepare('DELETE FROM chamados_suporte WHERE id = ?');
  $stmt->execute([$data['id'] ?? 0]);
  json_response(['ok' => true]);
}

require_auth('admin');
$stmt = $pdo->query('SELECT * FROM chamados_suporte ORDER BY criado_em DESC');
json_response(['ok' => true, 'chamados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
?>
