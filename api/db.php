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
  $url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
  if ($url) {
    $parts = parse_url($url);
    $host = $parts['host'] ?? 'localhost';
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'railway';
    $user = $parts['user'] ?? 'root';
    $pass = $parts['pass'] ?? '';
    $port = $parts['port'] ?? '3306';
  } else {
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $dbname = getenv('MYSQLDATABASE') ?: 'zlar';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    $port = getenv('MYSQLPORT') ?: '3306';
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
      'message' => 'Banco de dados indisponivel. Configure api/db.php com seus dados do MySQL.',
      'debug' => $e->getMessage()
    ], 500);
  }
}
?>
