<?php
require_once __DIR__ . '/db.php';
db()->query('SELECT 1');
json_response(['ok'=>true,'message'=>'Banco de dados disponível.']);
