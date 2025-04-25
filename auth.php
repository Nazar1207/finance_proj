<?php
require 'db.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $action = $_POST["action"];

    if ($action === "register") {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "Користувач вже існує.";
        } else {
            $username = explode("@", $email)[0];
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed])) {
                $_SESSION["user_id"] = $pdo->lastInsertId();
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Помилка при реєстрації.";
            }
        }
    } elseif ($action === "login") {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Невірний email або пароль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Finance App — Вхід / Реєстрація</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #111;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1s ease-in;
            color: #fff;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            max-width: 420px;
            background: #1a1a1a;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(255, 221, 0, 0.1);
            width: 90%;
        }

        .wrapper h2 {
            text-align: center;
            color: #ffdd00;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border-radius: 10px;
            background-color: #222;
            border: 1px solid #333;
            color: #fff;
            font-size: 16px;
            transition: 0.3s;
        }

        input:focus {
            border-color: #ffdd00;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 221, 0, 0.3);
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        button {
            flex: 1;
            padding: 12px;
            margin: 0 5px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            color: #000;
        }

        .login-btn {
            background: #ffdd00;
        }

        .register-btn {
            background: #ffd700;
        }

        button:hover {
            filter: brightness(1.1);
        }

        .message {
            background-color: #2c2c2c;
            color: #ff4d4d;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }

        .branding {
            text-align: center;
            margin-bottom: 30px;
        }

        .branding .emoji {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .branding h1 {
            margin: 0;
            font-size: 26px;
            color: #ffdd00;
        }

        .branding p {
            font-size: 14px;
            color: #bbb;
        }

        @media (max-width: 480px) {
            .buttons {
                flex-direction: column;
            }

            .buttons button {
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="branding">
        <div class="emoji">💸</div>
        <h1>Finance App</h1>
        <p>Реєструйся, відстежуй витрати, прокачуй фінграмотність</p>
    </div>
    <h2>Вхід / Реєстрація</h2>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Електронна пошта" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <div class="buttons">
            <button type="submit" name="action" value="login" class="login-btn">Увійти</button>
            <button type="submit" name="action" value="register" class="register-btn">Реєстрація</button>
        </div>
    </form>
</div>
</body>
</html>
