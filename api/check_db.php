<?php
require_once __DIR__ . '/db.php';

$connection = db_connection_info();

try {
  $pdo = db();
  $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
  json_response([
    'ok' => true,
    'message' => 'Conexao com o banco realizada com sucesso.',
    'connection' => [
      'host' => $connection['host'],
      'port' => $connection['port'],
      'database' => $connection['name'],
      'user' => $connection['user'],
    ],
    'tables' => $tables
  ]);
} catch (Throwable $e) {
  json_response([
    'ok' => false,
    'message' => 'Falha ao testar conexao. Confira se o MySQL do XAMPP esta ligado, se o banco zlar foi importado e se api/config.php esta correto.',
    'debug' => $e->getMessage(),
    'connection' => [
      'host' => $connection['host'],
      'port' => $connection['port'],
      'database' => $connection['name'],
      'user' => $connection['user'],
    ],
  ], 500);
}
?>
