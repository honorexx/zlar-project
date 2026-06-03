<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_auth('admin');

$data = input_json();
$action = $data['action'] ?? 'listar';
$tipo = $data['tipo'] ?? '';
$pdo = db();

if (!in_array($tipo, ['morador', 'prestador'], true)) {
  json_response(['ok' => false, 'message' => 'Tipo de usuario invalido.'], 422);
}

if ($action === 'listar') {
  if ($tipo === 'morador') {
    $stmt = $pdo->prepare('
      SELECT u.id, u.nome, u.email, u.telefone, u.status, m.cpf, m.endereco
      FROM usuarios u
      LEFT JOIN moradores m ON m.usuario_id = u.id
      WHERE u.tipo = "morador"
      ORDER BY u.nome
    ');
  } else {
    $stmt = $pdo->prepare('
      SELECT u.id, u.nome, u.email, u.telefone, u.status, p.servico, p.descricao, p.status_aprovacao
      FROM usuarios u
      LEFT JOIN prestadores p ON p.usuario_id = u.id
      WHERE u.tipo = "prestador"
      ORDER BY u.nome
    ');
  }
  $stmt->execute();
  $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
  json_response([
    'ok' => true,
    'tipo' => $tipo,
    'total' => count($usuarios),
    'usuarios' => $usuarios
  ]);
}

if ($action === 'atualizar') {
  $id = (int) ($data['id'] ?? 0);
  $nome = trim($data['nome'] ?? '');
  $email = trim($data['email'] ?? '');
  $telefone = trim($data['telefone'] ?? '');
  $status = trim($data['status'] ?? 'ativo');

  if (!$id || !$nome || !$email || !$telefone) {
    json_response(['ok' => false, 'message' => 'Preencha nome, e-mail e telefone.'], 422);
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'message' => 'Informe um e-mail valido.'], 422);
  }
  if (!in_array($status, ['ativo', 'inativo', 'bloqueado'], true)) {
    json_response(['ok' => false, 'message' => 'Status invalido.'], 422);
  }

  if ($tipo === 'morador') {
    $cpf = trim($data['cpf'] ?? '');
    $endereco = trim($data['endereco'] ?? '');
    if (!$cpf || !$endereco) {
      json_response(['ok' => false, 'message' => 'Preencha CPF e endereco.'], 422);
    }
  } else {
    $servico = trim($data['servico'] ?? '');
    $descricao = trim($data['descricao'] ?? '');
    $statusAprovacao = trim($data['status_aprovacao'] ?? 'em_analise');
    if (!$servico) {
      json_response(['ok' => false, 'message' => 'Preencha o servico do prestador.'], 422);
    }
    if (!in_array($statusAprovacao, ['em_analise', 'aprovado', 'reprovado', 'bloqueado'], true)) {
      json_response(['ok' => false, 'message' => 'Status de aprovacao invalido.'], 422);
    }
  }

  try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, status = ? WHERE id = ? AND tipo = ?');
    $stmt->execute([$nome, $email, $telefone, $status, $id, $tipo]);

    if ($tipo === 'morador') {
      $stmt = $pdo->prepare('UPDATE moradores SET cpf = ?, endereco = ? WHERE usuario_id = ?');
      $stmt->execute([$cpf, $endereco, $id]);
    } else {
      $stmt = $pdo->prepare('UPDATE prestadores SET servico = ?, descricao = ?, status_aprovacao = ? WHERE usuario_id = ?');
      $stmt->execute([$servico, $descricao, $statusAprovacao, $id]);
    }

    $pdo->commit();
    json_response(['ok' => true, 'message' => 'Usuario atualizado com sucesso.']);
  } catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($e->getCode() === '23000') {
      json_response(['ok' => false, 'message' => 'Ja existe usuario com este e-mail ou CPF.'], 409);
    }
    json_response(['ok' => false, 'message' => 'Nao foi possivel atualizar o usuario.'], 500);
  }
}

if ($action === 'excluir') {
  $id = (int) ($data['id'] ?? 0);
  if (!$id) {
    json_response(['ok' => false, 'message' => 'Usuario invalido.'], 422);
  }
  $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ? AND tipo = ?');
  $stmt->execute([$id, $tipo]);
  json_response(['ok' => true, 'message' => 'Usuario excluido com sucesso.']);
}

json_response(['ok' => false, 'message' => 'Acao invalida.'], 422);
?>
