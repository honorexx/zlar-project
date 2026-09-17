<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

function current_user(): ?array {
  return isset($_SESSION['zlar_user']) && is_array($_SESSION['zlar_user']) ? $_SESSION['zlar_user'] : null;
}

function refresh_session_user(array $user): array {
  if (($user['tipo'] ?? '') === 'admin') {
    $stmt = db()->prepare('SELECT id, usuario, nome, email, status FROM admin_acessos WHERE id = ? LIMIT 1');
    $stmt->execute([(int)($user['id'] ?? 0)]);
    $row = $stmt->fetch();
    if (!$row || $row['status'] !== 'ativo') return [];
    return ['id' => (int)$row['id'], 'tipo' => 'admin', 'nome' => $row['nome'], 'email' => $row['email'], 'usuario' => $row['usuario']];
  }

  $stmt = db()->prepare(
    'SELECT u.id, u.tipo, u.nome, u.email, u.telefone, u.status,
            m.cpf, m.nascimento AS morador_nascimento, m.endereco, m.cidade, m.estado,
            p.servico, p.descricao, p.nascimento AS prestador_nascimento,
            p.status_aprovacao, p.nota_media, p.total_avaliacoes
       FROM usuarios u
       LEFT JOIN moradores m ON m.usuario_id = u.id
       LEFT JOIN prestadores p ON p.usuario_id = u.id
      WHERE u.id = ? AND u.tipo = ? LIMIT 1'
  );
  $stmt->execute([(int)($user['id'] ?? 0), (string)($user['tipo'] ?? '')]);
  $row = $stmt->fetch();
  if (!$row || $row['status'] !== 'ativo') return [];
  $row['id'] = (int)$row['id'];
  $row['nascimento'] = $row['tipo'] === 'morador' ? $row['morador_nascimento'] : $row['prestador_nascimento'];
  unset($row['morador_nascimento'], $row['prestador_nascimento']);
  return $row;
}

function require_auth(?string $tipo = null): array {
  $user = current_user();
  if (!$user || ($tipo && ($user['tipo'] ?? '') !== $tipo)) {
    json_response(['ok' => false, 'message' => 'Acesso negado. Faça login para continuar.'], 401);
  }
  $lastActivity = (int)($_SESSION['last_activity'] ?? $_SESSION['login_at'] ?? 0);
  if ($lastActivity > 0 && time() - $lastActivity > 28800) {
    $_SESSION = [];
    session_destroy();
    json_response(['ok' => false, 'message' => 'Sua sessão expirou. Faça login novamente.'], 401);
  }
  $fresh = refresh_session_user($user);
  if (!$fresh) {
    unset($_SESSION['zlar_user']);
    json_response(['ok' => false, 'message' => 'Sua conta está inativa ou bloqueada.'], 403);
  }
  $version = credential_version($fresh);
  if (!isset($_SESSION['credential_version']) || !hash_equals($_SESSION['credential_version'], $version)) {
    $_SESSION = [];
    session_destroy();
    json_response(['ok'=>false,'message'=>'Sua credencial mudou. Faça login novamente.'],401);
  }
  $_SESSION['zlar_user'] = $fresh;
  $_SESSION['last_activity'] = time();
  return $fresh;
}

function require_approved_provider(): array {
  $user = require_auth('prestador');
  if (($user['status_aprovacao'] ?? '') !== 'aprovado') {
    json_response(['ok' => false, 'message' => 'Seu cadastro precisa estar aprovado e desbloqueado para acessar solicitações.'], 403);
  }
  return $user;
}

function credential_version(array $user): string {
  $sql = $user['tipo'] === 'admin'
    ? 'SELECT codigo_hash FROM admin_acessos WHERE id=?'
    : 'SELECT senha_hash FROM usuarios WHERE id=?';
  $stmt = db()->prepare($sql);
  $stmt->execute([$user['id']]);
  return hash('sha256', (string)$stmt->fetchColumn());
}

function login_user(array $user): void {
  session_regenerate_id(true);
  $_SESSION['zlar_user'] = $user;
  $_SESSION['credential_version'] = credential_version($user);
  $_SESSION['login_at'] = time();
  $_SESSION['last_activity'] = time();
}

function public_user(array $user): array {
  unset($user['senha_hash'], $user['codigo_hash'], $user['status']);
  return $user;
}
