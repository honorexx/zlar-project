<?php
require_once __DIR__ . '/db.php';

$data = input_json();
$action = $data['action'] ?? 'gerar';
$email = trim($data['email'] ?? '');
$tipo = trim($data['tipo'] ?? '');

if (!$email || !$tipo) {
  json_response(['ok' => false, 'message' => 'Informe e-mail e tipo de usuario.'], 422);
}

$pdo = db();

if ($action === 'redefinir') {
  $codigo = trim($data['codigo'] ?? '');
  $senha = trim($data['senha'] ?? '');
  $confirmarSenha = trim($data['confirmar_senha'] ?? '');

  if (!$codigo || !$senha || !$confirmarSenha) {
    json_response(['ok' => false, 'message' => 'Informe codigo, nova senha e confirmacao.'], 422);
  }
  if ($senha !== $confirmarSenha) {
    json_response(['ok' => false, 'message' => 'A confirmacao de senha nao confere.'], 422);
  }
  if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/\d/', $senha)) {
    json_response(['ok' => false, 'message' => 'A senha deve ter 8 caracteres, uma letra maiuscula e um numero.'], 422);
  }

  $stmt = $pdo->prepare('SELECT id FROM recuperacoes_senha WHERE email = ? AND tipo = ? AND codigo = ? AND usado = 0 AND expira_em >= NOW() ORDER BY id DESC LIMIT 1');
  $stmt->execute([$email, $tipo, $codigo]);
  $recuperacaoId = $stmt->fetchColumn();

  if (!$recuperacaoId) {
    json_response(['ok' => false, 'message' => 'Codigo invalido ou expirado. Gere um novo codigo.'], 422);
  }

  $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE email = ? AND tipo = ?');
  $stmt->execute([password_hash($senha, PASSWORD_DEFAULT), $email, $tipo]);

  if ($stmt->rowCount() < 1) {
    json_response(['ok' => false, 'message' => 'Usuario nao encontrado para este e-mail.'], 404);
  }

  $stmt = $pdo->prepare('UPDATE recuperacoes_senha SET usado = 1 WHERE id = ?');
  $stmt->execute([$recuperacaoId]);

  json_response(['ok' => true, 'message' => 'Senha redefinida com sucesso. Acesse o login com a nova senha.']);
}

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND tipo = ? LIMIT 1');
$stmt->execute([$email, $tipo]);
if (!$stmt->fetchColumn()) {
  json_response(['ok' => false, 'message' => 'Nao encontramos usuario com este e-mail.'], 404);
}

$codigo = (string) random_int(100000, 999999);
$expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$stmt = $pdo->prepare('INSERT INTO recuperacoes_senha (email, tipo, codigo, expira_em) VALUES (?, ?, ?, ?)');
$stmt->execute([$email, $tipo, $codigo, $expira]);

json_response([
  'ok' => true,
  'message' => 'Codigo de recuperacao gerado. Em producao, este codigo seria enviado por e-mail.',
  'codigo' => $codigo,
  'expira_em' => $expira
]);
?>
