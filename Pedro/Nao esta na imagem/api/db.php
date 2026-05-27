<?php
header('Content-Type: application/json; charset=utf-8');

function json_response($data, $status = 200) {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function input_json() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : $_POST;
}

function db() {
  $url = getenv('DATABASE_URL')
    ?: getenv('MYSQL_URL')
    ?: getenv('MYSQL_PUBLIC_URL')
    ?: getenv('DATABASE_PUBLIC_URL');

  if ($url) {
    $parts = parse_url($url);
    $host = $parts['host'] ?? 'localhost';
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'railway';
    $user = $parts['user'] ?? 'root';
    $pass = $parts['pass'] ?? '';
    $port = $parts['port'] ?? '3306';
  } else {
    $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'zephyr.proxy.rlwy.net';
    $dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
    $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
    $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '33060';
  }

  try {
    return new PDO(
      "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
      $user,
      $pass,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
  } catch (Throwable $e) {
    json_response([
      'ok' => false,
      'message' => 'Banco de dados indisponivel. Conexao tentada em ' . $host . ':' . $port . ' / banco ' . $dbname . '.',
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
?>
