<?php

include 'db_connection.php';

$db = $conn;

$db->query(<<<'SQL'
    CREATE TABLE IF NOT EXISTS employees (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_number VARCHAR(20) NOT NULL UNIQUE,
        job_title VARCHAR(80) NOT NULL
    ) ENGINE=InnoDB
    SQL);

$db->query(<<<'SQL'
    CREATE TABLE IF NOT EXISTS personal_information (
        employee_id INT UNSIGNED PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
    SQL);

echo "Migration complete.\n";
