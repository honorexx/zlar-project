<?php

return [
  'database' => [
    'host' => getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost',
    'port' => getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306',
    'name' => getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'zlar',
    'user' => getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root',
    'password' => getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
  ],
];
