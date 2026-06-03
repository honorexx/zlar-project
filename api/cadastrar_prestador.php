<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validators.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$data = input_json();
$nome = required_text($data, 'nome');
$email = required_text($data, 'email');
$telefone = required_text($data, 'telefone');
$senha = required_text($data, 'senha');
$confirmarSenha = required_text($data, 'confirmar_senha');
$nascimento = required_text($data, 'nascimento');
$servico = required_text($data, 'servico');
$descricao = required_text($data, 'descricao');

ensure_required([
  'nome' => $nome,
  'email' => $email,
  'telefone' => $telefone,
  'senha' => $senha,
  'confirmar senha' => $confirmarSenha,
  'data de nascimento' => $nascimento,
  'servico' => $servico,
]);
ensure_email($email);
ensure_phone($telefone);
ensure_password($senha, $confirmarSenha);
ensure_not_future_date($nascimento, 'A data de nascimento');

$pdo = db();
try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare('INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status) VALUES ("prestador", ?, ?, ?, ?, "ativo")');
  $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
  $usuarioId = $pdo->lastInsertId();

  $prestadorData = [
    'usuario_id' => $usuarioId,
    'servico' => $servico,
    'descricao' => $descricao,
    'status_aprovacao' => 'em_analise',
  ];
  if (table_has_column($pdo, 'prestadores', 'nascimento')) {
    $prestadorData['nascimento'] = $nascimento;
  }

  $camposIgnorados = ['nome', 'email', 'telefone', 'senha', 'confirmar_senha'];
  foreach ($data as $campo => $valor) {
    if (in_array($campo, $camposIgnorados, true) || array_key_exists($campo, $prestadorData)) continue;
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $campo)) continue;
    if (table_has_column($pdo, 'prestadores', $campo)) {
      $prestadorData[$campo] = $valor === '' ? null : $valor;
    }
  }

  $colunas = array_keys($prestadorData);
  $colunasSql = '`' . implode('`, `', $colunas) . '`';
  $placeholders = implode(', ', array_fill(0, count($colunas), '?'));
  $stmt = $pdo->prepare("INSERT INTO prestadores ($colunasSql) VALUES ($placeholders)");
  $stmt->execute(array_values($prestadorData));

  $pdo->commit();
  $user = [
    'id' => $usuarioId,
    'tipo' => 'prestador',
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
  ];
  foreach ($prestadorData as $campo => $valor) {
    if ($campo !== 'usuario_id') $user[$campo] = $valor;
  }
  $_SESSION['zlar_user'] = $user;
  json_response([
    'ok' => true,
    'id' => $usuarioId,
    'message' => 'Prestador cadastrado com sucesso.',
    'user' => $user
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($e instanceof PDOException && $e->getCode() === '23000') {
    json_response(['ok' => false, 'message' => 'Ja existe cadastro com este e-mail.'], 409);
  }
  json_response([
    'ok' => false,
    'message' => 'Nao foi possivel cadastrar o prestador.',
    'debug' => $e->getMessage()
  ], 500);
}
?>
