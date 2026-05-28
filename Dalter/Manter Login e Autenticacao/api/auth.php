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
  return $user;
}

function public_user($user) {
  unset($user['senha_hash'], $user['codigo']);
  return $user;
}
?>
