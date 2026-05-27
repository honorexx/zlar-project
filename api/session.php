<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$data = input_json();
$tipo = $data['tipo'] ?? null;
$user = require_auth($tipo);

json_response(['ok' => true, 'user' => public_user($user)]);
?>
