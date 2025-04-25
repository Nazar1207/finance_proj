<?php
include('db.php');
session_start();
$user_id = $_SESSION['user_id'] ?? 1; // заміни на авторизованого користувача

// Отримуємо всі категорії
$category_query = $pdo->prepare("SELECT id, name FROM categories");
$category_query->execute();
$categories = $category_query->fetchAll(PDO::FETCH_ASSOC);

// Визначаємо типи транзакцій
$transaction_types = ['income' => 'Доходи', 'expense' => 'Витрати'];

$chart_data = [];
$labels = [];

$selected_type = '';
$selected_category = 'all';
$start = '';
$end = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $selected_type = $_POST['transaction_type'];
    $selected_category = $_POST['category'];

    $params = [$start, $end, $selected_type, $user_id];
    $category_sql = "";

    if ($selected_category !== 'all') {
        $category_sql = "AND t.category_id = ?";
        $params[] = $selected_category;
    }

    $stmt = $pdo->prepare("
        SELECT c.name AS category_name, SUM(t.amount) AS total
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.date BETWEEN ? AND ?
          AND t.type = ?
          AND t.user_id = ?
          $category_sql
        GROUP BY c.name
    ");
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        $labels[] = $row['category_name'];
        $chart_data[] = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Статистика</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #333;
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #ffcc00;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        label, select, input {
            margin: 10px 0;
            font-size: 16px;
        }

        input, select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ffcc00;
            background-color: #333;
            color: #fff;
            width: 250px;
        }

        button {
            background-color: #ffcc00;
            color: #000;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #e6b800;
        }

        canvas {
            margin-top: 40px;
        }
    </style>
</head>
<body>
<div class="container">
<div style="text-align: left; margin-bottom: 10px;">
    <a href="dashboard.php" style="
        background-color: #ffcc00;
        color: #000;
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
    ">← Назад до дешборду</a>
</div>
    <h1>Статистика</h1>
    <form method="POST">
        <label for="start_date">Дата початку:</label>
        <input type="date" name="start_date" required value="<?= htmlspecialchars($start) ?>">

        <label for="end_date">Дата кінця:</label>
        <input type="date" name="end_date" required value="<?= htmlspecialchars($end) ?>">

        <label for="transaction_type">Тип транзакції:</label>
        <select name="transaction_type" required>
            <?php foreach ($transaction_types as $val => $label): ?>
                <option value="<?= $val ?>" <?= $selected_type === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="category">Категорія:</label>
        <select name="category" required>
            <option value="all" <?= $selected_category === 'all' ? 'selected' : '' ?>>Усі</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $selected_category == $cat['id'] ? 'selected' : '' ?>>
                    <?= $cat['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Показати</button>
    </form>

    <?php if (!empty($chart_data)): ?>
        <canvas id="pieChart"></canvas>
        <script>
            const ctx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        data: <?= json_encode($chart_data) ?>,
                        backgroundColor: [
                            '#ffcc00', '#ff9900', '#66cc33', '#3399ff', '#cc66ff', '#ff6666'
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });
        </script>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p style="text-align:center; margin-top: 30px;">Даних за обраними параметрами не знайдено.</p>
    <?php endif; ?>
</div>
</body>
</html>
