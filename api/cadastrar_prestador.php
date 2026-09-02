<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';

$servicos = ['Limpeza pesada', 'Limpeza diaria', 'Baba', 'Cuidador de idosos'];
$data = input_json();
$nome = required_text($data, 'nome');
$email = mb_strtolower(required_text($data, 'email'));
$telefone = required_text($data, 'telefone');
$nascimento = required_text($data, 'nascimento');
$servico = required_text($data, 'servico');
$descricao = trim((string)($data['descricao'] ?? ''));
$senha = required_text($data, 'senha');
$confirmacao = required_text($data, 'confirmar_senha');
ensure_email($email); ensure_phone($telefone); ensure_password($senha, $confirmacao); ensure_not_future_date($nascimento, 'A data de nascimento');
if (!in_array($servico, $servicos, true)) json_response(['ok' => false, 'message' => 'Serviço inválido.'], 422);

try {
  $id = transaction(function(PDO $pdo) use ($nome, $email, $telefone, $senha, $servico, $descricao, $nascimento) {
    $stmt = $pdo->prepare('INSERT INTO usuarios (tipo,nome,email,telefone,senha_hash) VALUES ("prestador",?,?,?,?)');
    $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO prestadores (usuario_id,servico,descricao,nascimento) VALUES (?,?,?,?)')->execute([$id, $servico, $descricao, $nascimento]);
    return $id;
  });
} catch (PDOException $e) {
  if ($e->getCode() === '23000') json_response(['ok' => false, 'message' => 'Já existe cadastro com este e-mail.'], 409);
  throw $e;
}
$user = refresh_session_user(['id' => $id, 'tipo' => 'prestador']);
login_user($user);
json_response(['ok' => true, 'id' => $id, 'message' => 'Prestador cadastrado e aguardando aprovação.', 'user' => public_user($user)], 201);
