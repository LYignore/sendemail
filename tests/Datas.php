<?php
use Lyignore\Sendemail\Datas;

$config = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '33060',
    'DB_DATABASE' => 'console_sys',
    'DB_USERNAME' => 'homestead',
    'DB_PASSWORD' => 'secret',
];

$db = new Datas($config);
$sql = "select * from `users`";
$datas = $db->select($sql);
var_dump($datas);