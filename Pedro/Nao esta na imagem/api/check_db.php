<?php
require_once __DIR__ . '/db.php';

$envNames = [
  'DATABASE_URL',
  'MYSQL_URL',
  'MYSQL_PUBLIC_URL',
  'DATABASE_PUBLIC_URL',
  'MYSQLHOST',
  'MYSQL_HOST',
  'MYSQLDATABASE',
  'MYSQL_DATABASE',
  'MYSQLUSER',
  'MYSQL_USER',
  'MYSQLPASSWORD',
  'MYSQL_ROOT_PASSWORD',
  'MYSQL_PASSWORD',
  'MYSQLPORT',
  'MYSQL_PORT'
];

$env = [];
foreach ($envNames as $name) {
  $value = getenv($name);
  if ($value === false || $value === '') {
    $env[$name] = 'nao encontrada';
  } elseif (stripos($name, 'PASSWORD') !== false || stripos($name, 'URL') !== false) {
    $env[$name] = 'encontrada';
  } else {
    $env[$name] = $value;
  }
}

try {
  $pdo = db();
  $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
  json_response([
    'ok' => true,
    'message' => 'Conexao com o banco realizada com sucesso.',
    'env' => $env,
    'tables' => $tables
  ]);
} catch (Throwable $e) {
  json_response([
    'ok' => false,
    'message' => 'Falha ao testar conexao com o banco.',
    'debug' => $e->getMessage(),
    'env' => $env
  ], 500);
}
?>
