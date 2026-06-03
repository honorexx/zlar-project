<?php

function required_text($data, $field) {
  return trim($data[$field] ?? '');
}

function ensure_required($fields) {
  foreach ($fields as $label => $value) {
    if ($value === '') {
      json_response([
        'ok' => false,
        'message' => 'Preencha o campo: ' . $label . '.'
      ], 422);
    }
  }
}

function ensure_email($email) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response([
      'ok' => false,
      'message' => 'Informe um e-mail válido.'
    ], 422);
  }
}

function ensure_phone($telefone) {
  if (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
    json_response([
      'ok' => false,
      'message' => 'Informe o telefone no formato (00) 00000-0000.'
    ], 422);
  }
}

function ensure_cpf($cpf) {
  if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
    json_response([
      'ok' => false,
      'message' => 'Informe o CPF no formato 000.000.000-00.'
    ], 422);
  }
}

function ensure_password($senha, $confirmarSenha) {
  if ($senha !== $confirmarSenha) {
    json_response([
      'ok' => false,
      'message' => 'A confirmação de senha não confere.'
    ], 422);
  }

  if (
    strlen($senha) < 8 ||
    !preg_match('/[A-Z]/', $senha) ||
    !preg_match('/\d/', $senha)
  ) {
    json_response([
      'ok' => false,
      'message' => 'A senha deve ter 8 caracteres, uma letra maiúscula e um número.'
    ], 422);
  }
}

function ensure_not_future_date($date, $label) {
  if ($date > date('Y-m-d')) {
    json_response([
      'ok' => false,
      'message' => $label . ' não pode ser no futuro.'
    ], 422);
  }
}