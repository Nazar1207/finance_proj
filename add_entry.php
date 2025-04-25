<?php
require 'db.php';

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category_id = $_POST['category'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $savings_amount = $_POST['savings_amount'] ?? 0;  // Заощадження

    // Вставка транзакції
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, category_id, description, date) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $amount, $type, $category_id, $description, $date]);

    // Додавання заощаджень
    if ($savings_amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO savings (user_id, category, amount, created_at) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $type, $savings_amount]);
    }

    header("Location: dashboard.php");
    exit();
}

// Отримуємо категорії доходів та витрат
$stmt = $pdo->prepare("SELECT * FROM categories WHERE type IN ('income', 'expense')");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати запис — Finance App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #0f0f0f;
            color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background: #1a1a1a;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 0 40px rgba(255, 255, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 36px;
            color: #f1f1f1;
            text-shadow: 0 0 5px #ff0;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 18px;
            color: #aaa;
        }

        form {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }

        form div {
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #f5f5f5;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            background: #222;
            color: #fff;
            border: 1px solid #333;
            border-radius: 12px;
            font-size: 16px;
            margin-top: 8px;
        }

        input[type="number"], input[type="date"] {
            width: 100%;
            padding: 12px;
            background: #222;
            color: #fff;
            border: 1px solid #333;
            border-radius: 12px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            padding: 14px 24px;
            font-size: 16px;
            background: #111;
            color: #ff0;
            border: 2px solid #ff0;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            text-shadow: 0 0 5px #ff0;
        }

        button:hover {
            background: #222;
            box-shadow: 0 0 12px rgba(255, 255, 0, 0.4);
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            width: 100%;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .footer a {
            color: #ff0;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Додати запис</h1>
    </div>

    <form method="POST">
        <div>
            <label for="amount">Сума:</label>
            <input type="number" name="amount" id="amount" required>
        </div>

        <div>
            <label for="type">Тип:</label>
            <select name="type" id="type" required>
                <option value="income">Доходи</option>
                <option value="expense">Витрати</option>
            </select>
        </div>

        <div>
            <label for="category">Категорія:</label>
            <select name="category" id="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="description">Опис:</label>
            <textarea name="description" id="description"></textarea>
        </div>

        <div>
            <label for="date">Дата:</label>
            <input type="date" name="date" id="date" required>
        </div>

        <div>
            <label for="savings_amount">Заощадження:</label>
            <input type="number" name="savings_amount" id="savings_amount" placeholder="Сума заощаджень">
        </div>

        <div>
            <button type="submit">Зберегти запис</button>
        </div>
    </form>

    <div class="buttons">
        <button onclick="window.location.href='dashboard.php'">Назад</button>
    </div>
</div>
</body>
</html>
