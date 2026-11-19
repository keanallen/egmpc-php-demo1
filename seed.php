<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require __DIR__ . '/db_connection.php';

$employee = $conn->prepare(<<<'SQL'
    INSERT INTO employees (employee_number, job_title)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), job_title = ?
    SQL);

$personalInfo = $conn->prepare(<<<'SQL'
    INSERT INTO personal_information (employee_id, first_name, last_name)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE first_name = ?, last_name = ?
    SQL);

$firstNames = [
    'Ana',
    'Miguel',
    'Sofia',
    'Paolo',
    'Isabel',
    'Gabriel',
    'Camille',
    'Adrian',
    'Bianca',
    'Daniel',
    'Elena',
    'Rafael',
    'Julia',
    'Marco',
    'Angela',
    'Lucas',
    'Diana',
    'Vincent',
    'Clara',
    'Jose',
    'Teresa',
    'Andres',
    'Mia',
    'Nathan',
    'Patricia',
    'Joaquin',
    'Alyssa',
    'Carlos',
    'Louise',
    'Emilio',
    'Nina',
    'Antonio',
    'Celeste',
    'Joshua',
    'Beatriz',
    'Martin',
    'Grace',
    'Francis',
    'Lara',
    'Enrique',
    'Carmen',
    'Ethan',
    'Leah',
    'Noel',
    'Hannah',
    'Luis',
    'Andrea',
    'Samuel',
    'Monica',
    'Victor',
];

$lastNames = [
    'Reyes',
    'Santos',
    'Cruz',
    'Garcia',
    'Mendoza',
    'Ramos',
    'Bautista',
    'Flores',
    'Gonzales',
    'Aquino',
    'Villanueva',
    'Fernandez',
    'Castillo',
    'Rivera',
    'Torres',
    'Domingo',
    'Mercado',
    'Navarro',
    'Salazar',
    'Valdez',
    'Aguilar',
    'Pascual',
    'Padilla',
    'Morales',
    'Santiago',
    'Gutierrez',
    'Del Rosario',
    'De Guzman',
    'De Leon',
    'Dela Cruz',
    'Soriano',
    'Cabrera',
    'Marquez',
    'Robles',
    'Velasco',
    'Serrano',
    'Manalo',
    'David',
    'Tan',
    'Lim',
    'Chua',
    'Sy',
    'Yap',
    'Co',
    'Ong',
    'Abad',
    'Cortez',
    'Pineda',
    'Estrada',
    'Miranda',
];
$jobTitles = ['Security Guard', 'Property Manager', 'Supervisor'];

$conn->begin_transaction();

try {
    for ($i = 0; $i < 60000; $i++) {
        $employeeNumber = sprintf('DEMO-%04d', $i + 1);
        $jobTitle = $jobTitles[$i % count($jobTitles)];


        $firstNameIndex = $i % count($firstNames);
        $batch = intdiv($i, count($firstNames));
        $lastNameIndex = ($firstNameIndex + $batch) % count($lastNames);

        $firstName = $firstNames[$firstNameIndex];
        $lastName = $lastNames[$lastNameIndex];

        $employee->execute([$employeeNumber, $jobTitle, $jobTitle]);
        $employeeId = $conn->insert_id;

        $personalInfo->execute([
            $employeeId,
            $firstName,
            $lastName,
            $firstName,
            $lastName,
        ]);
    }

    $conn->commit();
    echo "Seeded 60,000 employees and 60,000 personal information records.\n";
} catch (Throwable $exception) {
    $conn->rollback();
    throw $exception;
}
