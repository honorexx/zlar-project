<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function json_response(array $data, int $status = 200): never {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

set_exception_handler(function(Throwable $e): never {
  error_log('Zlar unhandled error: ' . $e->getMessage());
  json_response(['ok' => false, 'message' => 'Ocorreu um erro interno. Tente novamente.'], 500);
});

function input_json(): array {
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Método não permitido.'], 405);
  }
  $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
  if (!str_contains($contentType, 'application/json')) {
    json_response(['ok' => false, 'message' => 'Envie os dados em JSON.'], 415);
  }
  $raw = file_get_contents('php://input');
  $data = json_decode($raw ?: '', true);
  if (!is_array($data)) {
    json_response(['ok' => false, 'message' => 'JSON inválido.'], 400);
  }
  return $data;
}

function db_config(): array {
  $config = require __DIR__ . '/config.php';
  return $config['database'];
}

function db_connection_info(): array {
  $config = db_config();
  $url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('MYSQL_PUBLIC_URL') ?: getenv('DATABASE_PUBLIC_URL');
  if (!$url) return $config;
  $parts = parse_url($url);
  return [
    'host' => $parts['host'] ?? $config['host'],
    'port' => (string)($parts['port'] ?? $config['port']),
    'name' => isset($parts['path']) ? ltrim($parts['path'], '/') : $config['name'],
    'user' => $parts['user'] ?? $config['user'],
    'password' => $parts['pass'] ?? $config['password'],
  ];
}

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;
  $c = db_connection_info();
  try {
    $pdo = new PDO(
      "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4",
      $c['user'],
      $c['password'],
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]
    );
    return $pdo;
  } catch (Throwable $e) {
    error_log('Zlar database error: ' . $e->getMessage());
    json_response(['ok' => false, 'message' => 'Banco de dados indisponível. Confira a configuração do MySQL.'], 503);
  }
}

function text_field(array $data, string $key, int $max = 255): string {
  $value = trim((string)($data[$key] ?? ''));
  if ($value === '') json_response(['ok' => false, 'message' => "Preencha o campo {$key}."], 422);
  if (mb_strlen($value) > $max) json_response(['ok' => false, 'message' => "O campo {$key} é muito longo."], 422);
  return $value;
}

function optional_text(array $data, string $key, int $max = 2000): string {
  $value = trim((string)($data[$key] ?? ''));
  if (mb_strlen($value) > $max) json_response(['ok' => false, 'message' => "O campo {$key} é muito longo."], 422);
  return $value;
}

function transaction(callable $callback) {
  $pdo = db();
  try {
    $pdo->beginTransaction();
    $result = $callback($pdo);
    $pdo->commit();
    return $result;
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}
