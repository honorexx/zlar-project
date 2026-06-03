<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_user() {
  return $_SESSION['zlar_user'] ?? null;
}

function require_auth($tipo = null) {
  $user = current_user();
  if (!$user || ($tipo && ($user['tipo'] ?? '') !== $tipo)) {
    json_response(['ok' => false, 'message' => 'Acesso negado. Faca login para continuar.'], 401);
  }

  if (function_exists('db') && in_array($user['tipo'] ?? '', ['morador', 'prestador'], true)) {
    $stmt = db()->prepare('SELECT id FROM usuarios WHERE id = ? AND tipo = ? LIMIT 1');
    $stmt->execute([$user['id'] ?? 0, $user['tipo']]);
    if (!$stmt->fetch()) {
      unset($_SESSION['zlar_user']);
      json_response(['ok' => false, 'message' => 'Sessao expirada. Faca login novamente.'], 401);
    }
  }

  return $user;
}

function public_user($user) {
  unset($user['senha_hash'], $user['codigo']);
  return $user;
}
?>
