<?php

$startedAt = hrtime(true);

require __DIR__ . '/vendor/autoload.php';

$redis = new \Predis\Client([
    'host' => 'redis',
    'port' => 6379,
]);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 100000],
]) ?: 1;

$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$fetchLimit = $pageSize + 1;

$cacheKey = "demo:employees:page:$page:size:$pageSize";
$cachedPage = $redis->get($cacheKey);
$queryCount = 0;

if ($cachedPage !== null) {
    $pageData = json_decode($cachedPage, true, 512, JSON_THROW_ON_ERROR);
    $source = 'Redis cache';
} else {
    require __DIR__ . '/db_connection.php';

    $employeeSql = 'SELECT e.employee_number, e.job_title,
                           p.first_name, p.last_name
                    FROM employees AS e
                    INNER JOIN personal_information AS p ON p.employee_id = e.id
                    ORDER BY e.employee_number
                    LIMIT ? OFFSET ?';

    $statement = $conn->prepare($employeeSql);
    $statement->bind_param('ii', $fetchLimit, $offset);
    $statement->execute();

    $result = $statement->get_result();
    $employees = $result->fetch_all(MYSQLI_ASSOC);
    $queryCount = 1;
    $source = 'MySQL';

    // The extra row tells us whether there is a next page.
    $hasNextPage = count($employees) > $pageSize;

    if ($hasNextPage) {
        array_pop($employees);
    }

    $pageData = [
        'employees' => $employees,
        'hasNextPage' => $hasNextPage,
    ];

    if ($employees !== []) {
        $redis->setex($cacheKey, 300, json_encode($pageData, JSON_THROW_ON_ERROR));
    }
}

$employees = $pageData['employees'];
$hasNextPage = $pageData['hasNextPage'];
$dataLoadMs = (hrtime(true) - $startedAt) / 1_000_000;

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

        <nav aria-label="Employee pages">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>

            <span>Page <?= $page ?></span>

            <?php if ($hasNextPage): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </nav>

        <p><strong>Employees on this page</strong> <?= count($employees) ?></p>
        <p><strong>Data source</strong> <?= escape($source) ?></p>
        <p><strong>MySQL SELECTs</strong> <?= $queryCount ?></p>
        <p><strong>Data load time</strong> <?= number_format($dataLoadMs, 2) ?> ms</p>
    </main>
</body>

</html>