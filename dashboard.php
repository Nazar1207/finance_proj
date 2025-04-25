<?php
require 'db.php';

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT username, email, avatar_url, balance, income_total, expenses_total, level, xp FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$username = $user['username'];
$email = $user['email'];
$avatar_url = $user['avatar_url'];
$balance = number_format($user['balance'], 2);
$income_total = number_format($user['income_total'], 2);
$expenses_total = number_format($user['expenses_total'], 2);
$level = $user['level'];
$xp = $user['xp'];

// Порада дня
$tips = [
    "Завжди відкладайте 10% від доходу для себе.",
    "Використовуйте правило 50/30/20 для планування бюджету.",
    "Перед великою покупкою зробіть паузу на 24 години.",
    "Ведіть облік витрат щодня — це відкриває очі на фінанси.",
    "Користуйтесь кешбеком і бонусними програмами з розумом.",
    "Не тримайте всі гроші на карті — це спокуса витрачати.",
    "Автоматизуйте накопичення: навіть 10 грн на день — це прогрес.",
];
$random_tip = $tips[array_rand($tips)];

// Заощадження
$stmt = $pdo->prepare("SELECT SUM(amount) AS total_savings FROM savings WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_savings = $stmt->fetchColumn();
$total_savings = number_format($total_savings, 2);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Finance App — Dashboard</title>
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

        .profile {
            position: absolute;
            top: 25px;
            right: 25px;
            display: flex;
            align-items: center;
            background: #222;
            border-radius: 30px;
            padding: 10px 20px;
            box-shadow: 0 0 10px rgba(255, 255, 0, 0.1);
            border: 1px solid #333;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-info strong {
            font-size: 16px;
            color: #fff;
        }

        .profile-info small {
            color: #ccc;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            margin: 30px 0;
        }

        .stat-card {
            background: #111;
            color: #fffbcc;
            border: 1px solid #333;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 0 15px rgba(255, 255, 0, 0.2);
        }

        .stat-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #fff;
            text-shadow: 0 0 3px #ff0;
        }

        .stat-card p {
            font-size: 20px;
            font-weight: bold;
            color: #ff0;
        }

        .tip-box {
            background: #222;
            border-left: 6px solid #ff0;
            color: #fff;
            border-radius: 20px;
            padding: 25px;
            margin-top: 10px;
            width: 100%;
            box-shadow: 0 0 15px rgba(255, 255, 0, 0.1);
            animation: fadeInUp 1s ease;
            text-align: center;
        }

        .tip-box h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #ff0;
            text-shadow: 0 0 5px #ff0;
        }

        .tip-box p {
            font-size: 16px;
            color: #eee;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            width: 100%;
        }

        .buttons button {
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

        .buttons button:hover {
            background: #222;
            box-shadow: 0 0 12px rgba(255, 255, 0, 0.4);
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

        @media (max-width: 768px) {
            .profile {
                position: static;
                margin-bottom: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
                align-items: center;
            }

            .buttons button {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
<div class="container">
<div class="profile">
    <img src="<?= htmlspecialchars($avatar_url) ?>" alt="avatar">
    <div class="profile-info">
        <strong><?= htmlspecialchars($username) ?></strong>
        <?php
        // Обчислення XP прогресу
        $xp_needed = 500;  // XP, необхідний для переходу на наступний рівень
        $current_level_xp = $xp % $xp_needed;  // XP, який залишився на поточному рівні
        ?>
        <small>Рівень <?= $level ?> | XP: <?= $current_level_xp ?>/500</small>
    </div>
</div>

    <div class="header">
        <h1>Привіт, <?= htmlspecialchars($username) ?>!</h1>
        <h2>Ось твоя фінансова статистика</h2>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h3>Доходи</h3>
            <p><?= $income_total ?> грн</p>
        </div>
        <div class="stat-card">
            <h3>Витрати</h3>
            <p><?= $expenses_total ?> грн</p>
        </div>
        <div class="stat-card">
            <h3>Баланс</h3>
            <p><?= $balance ?> грн</p>
        </div>
    </div>

    <div class="stat-card" style="width: 100%; background: #181818;">
        <h3>💰 Мої заощадження</h3>
        <p>Загальна сума: <strong><?= $total_savings ?> грн</strong></p>
    </div>

    <div class="tip-box">
        <h3>💡 Порада дня</h3>
        <p><?= $random_tip ?></p>
    </div>

    <div class="buttons">
        <button onclick="window.location.href='add_entry.php'">➕ Додати запис</button>
        <button onclick="window.location.href='statistics.php'">📊 Статистика</button>
        <button onclick="window.location.href='challenges.php'">🧩 Челенджі</button>
        <button onclick="window.location.href='lessons.php'">📘 Уроки</button>
        <button onclick="window.location.href='profile.php'">👤 Профіль</button>
        <button onclick="window.location.href='logout.php'">🚪 Вийти</button>
    </div>

    <div class="footer">
        <p>© 2025 Finance App. <a href="#">Політика конфіденційності</a></p>
    </div>
</div>
</body>
</html>
