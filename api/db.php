<?php
header('Content-Type: application/json; charset=utf-8');

function json_response($data, $status = 200) {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

set_exception_handler(function($e) {
  json_response([
    'ok' => false,
    'message' => 'Erro interno no PHP.',
    'debug' => $e->getMessage()
  ], 500);
});

register_shutdown_function(function() {
  $error = error_get_last();
  if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
    return;
  }
  json_response([
    'ok' => false,
    'message' => 'Erro fatal no PHP.',
    'debug' => $error['message']
  ], 500);
});

function input_json() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : $_POST;
}

function db_config() {
  $config = require __DIR__ . '/config.php';
  return $config['database'];
}

function db_connection_info() {
  $config = db_config();

  $url = getenv('DATABASE_URL')
    ?: getenv('MYSQL_URL')
    ?: getenv('MYSQL_PUBLIC_URL')
    ?: getenv('DATABASE_PUBLIC_URL');

  if (!$url) {
    return $config;
  }

  $parts = parse_url($url);
  return [
    'host' => $parts['host'] ?? $config['host'],
    'port' => (string)($parts['port'] ?? $config['port']),
    'name' => isset($parts['path']) ? ltrim($parts['path'], '/') : $config['name'],
    'user' => $parts['user'] ?? $config['user'],
    'password' => $parts['pass'] ?? $config['password'],
  ];
}

function db() {
  $connection = db_connection_info();
  $host = $connection['host'];
  $port = $connection['port'];
  $dbname = $connection['name'];
  $user = $connection['user'];
  $pass = $connection['password'];

  try {
    return new PDO(
      "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
      $user,
      $pass,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
    );
  } catch (Throwable $e) {
    json_response([
      'ok' => false,
      'message' => 'Banco de dados indisponivel. Confira o XAMPP/MySQL e o arquivo api/config.php.',
      'debug' => $e->getMessage(),
      'connection' => [
        'host' => $host,
        'port' => $port,
        'database' => $dbname,
        'user' => $user
      ]
    ], 500);
  }
}

function table_has_column($pdo, $table, $column) {
  $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
  $stmt->execute([$column]);
  return (bool)$stmt->fetch();
}
?>
