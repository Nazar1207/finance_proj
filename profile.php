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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['email']) && $_POST['email'] !== $email) {
        $new_email = $_POST['email'];
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
        $email = $new_email;
    }

    if (!empty($_POST['username']) && $_POST['username'] !== $username) {
        $new_username = $_POST['username'];
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $user_id]);
        $username = $new_username;
    }

    if (!empty($_POST['password']) && !empty($_POST['confirm_password']) && $_POST['password'] === $_POST['confirm_password']) {
        $new_password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$new_password_hash, $user_id]);
    }

    if (!empty($_FILES['avatar']['tmp_name'])) {
        $avatar_dir = 'uploads/avatars/';
        if (!is_dir($avatar_dir)) {
            mkdir($avatar_dir, 0755, true);
        }
        $avatar_filename = $avatar_dir . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_filename)) {
            $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $stmt->execute([$avatar_filename, $user_id]);
            $avatar_url = $avatar_filename;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Finance App — Профіль</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #000;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            background-color: #111;
            border-radius: 20px;
            padding: 40px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #f7e600;
        }

        .header h2 {
            font-size: 1.2rem;
            color: #ccc;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-info img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid #f7e600;
            object-fit: cover;
            margin-bottom: 15px;
            box-shadow: 0 0 15px rgba(255, 255, 0, 0.3);
        }

        .profile-info h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .profile-info p {
            font-size: 1rem;
            margin: 4px 0;
            color: #aaa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #f7e600;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background-color: #1a1a1a;
            color: #fff;
            font-size: 1rem;
            box-shadow: inset 0 0 5px rgba(255, 255, 0, 0.1);
            transition: 0.3s;
        }

        .form-group input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(255, 255, 0, 0.6);
        }

        .form-group button {
            width: 100%;
            padding: 12px;
            background-color: #f7e600;
            color: #000;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .form-group button:hover {
            background-color: #fff700;
            box-shadow: 0 0 15px rgba(255, 255, 0, 0.5);
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #888;
        }

        .footer a {
            color: #f7e600;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Привіт, <?= htmlspecialchars($username) ?>!</h1>
            <h2>Це твій профіль</h2>
        </div>

        <div class="profile-info">
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar">
            <h2><?= htmlspecialchars($username) ?></h2>
            <p>Email: <?= htmlspecialchars($email) ?></p>
            <p>Баланс: <?= $balance ?> грн</p>
            <p>Доходи: <?= $income_total ?> грн</p>
            <p>Витрати: <?= $expenses_total ?> грн</p>
            <?php
$xp_needed = 500;  // XP, необхідний для переходу на наступний рівень
$current_level_xp = $xp % $xp_needed;  // XP, який залишився на поточному рівні
?>
<p>Рівень: <?= $level ?> | XP: <?= $current_level_xp ?>/500</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Ім’я користувача</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Нове ім’я користувача">
            </div>

            <div class="form-group">
                <label for="email">Нова електронна пошта</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Нова електронна пошта">
            </div>

            <div class="form-group">
                <label for="password">Новий пароль</label>
                <input type="password" id="password" name="password" placeholder="Новий пароль">
            </div>

            <div class="form-group">
                <label for="confirm_password">Підтвердження пароля</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Підтвердження пароля">
            </div>

            <div class="form-group">
                <label for="avatar">Новий аватар</label>
                <input type="file" id="avatar" name="avatar">
            </div>

            <div class="form-group">
                <button type="submit">Зберегти зміни</button>
            </div>
        </form>

        <div class="footer">
            <a href="dashboard.php">🔙 Назад до Dashboard</a>
        </div>
    </div>
</body>
</html>