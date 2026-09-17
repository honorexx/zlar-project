<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$data = input_json();
$tipo = trim((string)($data['tipo'] ?? ''));
$identificador = trim((string)($data['email'] ?? ''));
$senha = (string)($data['senha'] ?? '');
if (!in_array($tipo, ['morador', 'prestador', 'admin'], true) || $identificador === '' || $senha === '') {
  json_response(['ok' => false, 'message' => 'Informe usuário/e-mail e senha.'], 422);
}

if ($tipo === 'admin') {
  $stmt = db()->prepare('SELECT id, usuario, nome, email, codigo_hash, status FROM admin_acessos WHERE usuario = ? LIMIT 1');
  $stmt->execute([$identificador]);
  $row = $stmt->fetch();
  if (!$row || $row['status'] !== 'ativo' || !password_verify($senha, $row['codigo_hash'])) {
    json_response(['ok' => false, 'message' => 'Usuário ou código de acesso inválido.'], 401);
  }
  $user = ['id' => (int)$row['id'], 'tipo' => 'admin', 'nome' => $row['nome'], 'email' => $row['email'], 'usuario' => $row['usuario']];
  login_user($user);
  json_response(['ok' => true, 'user' => $user]);
}

$stmt = db()->prepare('SELECT id, tipo, nome, email, telefone, senha_hash, status FROM usuarios WHERE email = ? AND tipo = ? LIMIT 1');
$stmt->execute([mb_strtolower($identificador), $tipo]);
$row = $stmt->fetch();
if (!$row || !password_verify($senha, $row['senha_hash'])) {
  json_response(['ok' => false, 'message' => 'E-mail ou senha inválidos.'], 401);
}
if ($row['status'] !== 'ativo') {
  json_response(['ok' => false, 'message' => 'Sua conta está inativa ou bloqueada.'], 403);
}
$user = refresh_session_user(['id' => (int)$row['id'], 'tipo' => $tipo]);
login_user($user);
json_response(['ok' => true, 'user' => public_user($user)]);
