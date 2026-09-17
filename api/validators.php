<?php

function required_text($data, $field) {
  $limits = ['nome'=>160,'email'=>160,'telefone'=>30,'cpf'=>20,'nascimento'=>10,'endereco'=>255,'cargo'=>80,'servico'=>120];
  return text_field($data, $field, $limits[$field] ?? 255);
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
  ensure_date($date, $label);
  if ($date > date('Y-m-d')) {
    json_response([
      'ok' => false,
      'message' => $label . ' não pode ser no futuro.'
    ], 422);
  }
}
function ensure_date(string $date, string $label): void {
  $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
  if (!$parsed || $parsed->format('Y-m-d') !== $date) {
    json_response(['ok'=>false,'message'=>$label . ' deve ser uma data válida.'],422);
  }
}

function payment_amount($value): string {
  if (!is_string($value) && !is_int($value) && !is_float($value)) {
    json_response(['ok'=>false,'message'=>'Valor de pagamento inválido.'],422);
  }
  $raw = trim((string)$value);
  if (preg_match('/^(?:[0-9]+|[1-9][0-9]{0,2}(?:\.[0-9]{3})+),[0-9]{1,2}$/D', $raw)) {
    $raw = str_replace(',', '.', str_replace('.', '', $raw));
  } elseif (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $raw)) {
    json_response(['ok'=>false,'message'=>'Informe um valor como 189,90 ou 189.90.'],422);
  }
  $amount = (float)$raw;
  if ($amount < 0.01 || $amount > 99999999.99) {
    json_response(['ok'=>false,'message'=>'Informe um valor de pagamento válido.'],422);
  }
  return number_format($amount, 2, '.', '');
}
