<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$data = input_json();
$tipo = $data['tipo'] ?? '';
$email = trim($data['email'] ?? '');
$senha = trim($data['senha'] ?? '');

if (!$tipo || !$email || !$senha) {
  json_response(['ok' => false, 'message' => 'Informe tipo, e-mail e senha.'], 422);
}

$pdo = db();

if ($tipo === 'admin') {
  $stmt = $pdo->prepare('SELECT usuario, codigo, status FROM admin_acessos WHERE usuario = ? LIMIT 1');
  $stmt->execute([$email]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$admin || $admin['status'] !== 'ativo' || !hash_equals($admin['codigo'], $senha)) {
    json_response(['ok' => false, 'message' => 'Usuario ou codigo de acesso invalido.'], 401);
  }
  $user = [
    'id' => 0,
    'tipo' => 'admin',
    'nome' => 'Administrador Zlar',
    'email' => $admin['usuario']
  ];
  $_SESSION['zlar_user'] = $user;
  json_response(['ok' => true, 'user' => $user]);
}

$stmt = $pdo->prepare('SELECT id, nome, email, tipo, senha_hash FROM usuarios WHERE email = ? AND tipo = ? LIMIT 1');
$stmt->execute([$email, $tipo]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($senha, $user['senha_hash'])) {
  json_response(['ok' => false, 'message' => 'Login invalido.'], 401);
}

unset($user['senha_hash']);
if ($tipo === 'prestador') {
  $stmt = $pdo->prepare('SELECT servico, descricao FROM prestadores WHERE usuario_id = ? LIMIT 1');
  $stmt->execute([$user['id']]);
  $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($perfil) $user = array_merge($user, $perfil);
}
if ($tipo === 'morador') {
  $stmt = $pdo->prepare('SELECT cpf, endereco FROM moradores WHERE usuario_id = ? LIMIT 1');
  $stmt->execute([$user['id']]);
  $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($perfil) $user = array_merge($user, $perfil);
}
$_SESSION['zlar_user'] = $user;
json_response(['ok' => true, 'user' => $user]);
?>
