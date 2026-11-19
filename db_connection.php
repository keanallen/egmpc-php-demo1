<?php

$host = 'mysql';
$port = 3306;
$database = 'mydb';
$username = 'localuser';
$password = '12345678';

$conn = new mysqli($host, $username, $password, $database, $port);
$conn->set_charset('utf8mb4');
