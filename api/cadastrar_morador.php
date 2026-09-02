<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';

$data = input_json();
$nome = required_text($data, 'nome');
$email = mb_strtolower(required_text($data, 'email'));
$telefone = required_text($data, 'telefone');
$cpf = required_text($data, 'cpf');
$nascimento = required_text($data, 'nascimento');
$endereco = required_text($data, 'endereco');
$senha = required_text($data, 'senha');
$confirmacao = required_text($data, 'confirmar_senha');
ensure_required(compact('nome', 'email', 'telefone', 'cpf', 'nascimento', 'endereco', 'senha'));
ensure_email($email); ensure_phone($telefone); ensure_cpf($cpf); ensure_password($senha, $confirmacao); ensure_not_future_date($nascimento, 'A data de nascimento');

try {
  $id = transaction(function(PDO $pdo) use ($nome, $email, $telefone, $senha, $cpf, $nascimento, $endereco) {
    $stmt = $pdo->prepare('INSERT INTO usuarios (tipo,nome,email,telefone,senha_hash) VALUES ("morador",?,?,?,?)');
    $stmt->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO moradores (usuario_id,cpf,nascimento,endereco) VALUES (?,?,?,?)')->execute([$id, $cpf, $nascimento, $endereco]);
    return $id;
  });
} catch (PDOException $e) {
  if ($e->getCode() === '23000') json_response(['ok' => false, 'message' => 'Já existe cadastro com este e-mail ou CPF.'], 409);
  throw $e;
}
$user = refresh_session_user(['id' => $id, 'tipo' => 'morador']);
login_user($user);
json_response(['ok' => true, 'id' => $id, 'message' => 'Morador cadastrado com sucesso.', 'user' => public_user($user)], 201);
