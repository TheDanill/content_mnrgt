<?php

$config = [
  'db' => [
      'server' => 'localhost',
      'database' => 'topstar',
      'login' => 'topstar',
      'pass' => 'bacrUpheq+#ac8A',
      'port' => 3306,
      'charset' => 'utf8mb4'
  ],
  'cache' => [
      'server' => '127.0.0.1',
      'port' => 6379
  ],
  'queue' => [
      'server' => '127.0.0.1',
      'port' => 11211
  ],
  "password_salt" => '$6$rounds=5000$nUWL1AzjBeoDU29UsJOu1uNn8LudSAQh8X5soy1EpJqsqRIFF9m0m$',
  'domain' => 'wpscan.foxovsky.ru',
  'default_language' => 'ru',
  'per_page' => 20,
  'max_per_page' => 100
    
];