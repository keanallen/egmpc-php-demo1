<?php

$startedAt = hrtime(true);

require __DIR__ . '/db_connection.php';

// Query 1: Get all employees.
$employeeSql = 'SELECT id, employee_number, job_title
                FROM employees
                ORDER BY employee_number';

$employeeResult = $conn->query($employeeSql);
$employeeRows = $employeeResult->fetch_all(MYSQLI_ASSOC);
$queryCount = 1;

// Prepare the query we will run for each employee.
$personalInfoSql = 'SELECT first_name, last_name
                    FROM personal_information
                    WHERE employee_id = ?';

$personalInfoStatement = $conn->prepare($personalInfoSql);
$employees = [];

foreach ($employeeRows as $employee) {
    $employeeId = $employee['id'];

    // Run the query using this employee's ID.
    $personalInfoStatement->execute([$employeeId]);
    $queryCount++;

    $personalInfoResult = $personalInfoStatement->get_result();
    $personalInfo = $personalInfoResult->fetch_assoc();

    $employee['first_name'] = $personalInfo['first_name'] ?? '—';
    $employee['last_name'] = $personalInfo['last_name'] ?? '—';

    $employees[] = $employee;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            margin: 2rem;
        }

        main {
            max-width: 960px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.65rem;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #eef2f6;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fb;
        }
    </style>
</head>

<body>
    <main>
        <h1>Employee Dashboard</h1>

        <table>
            <thead>
                <tr>
                    <th scope="col">Employee Number</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Job Title</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?= escape($employee['employee_number']) ?></td>
                        <td><?= escape($employee['first_name']) ?></td>
                        <td><?= escape($employee['last_name']) ?></td>
                        <td><?= escape($employee['job_title']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total records</strong> <?= number_format(count($employees)) ?></p>
        <p><strong>Server-side response time:</strong>
            <?= number_format((hrtime(true) - $startedAt) / 1_000_000, 2) ?> ms
        </p>
    </main>
</body>

</html>