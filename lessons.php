<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Завершення уроку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_lesson_id'])) {
    $lesson_id = intval($_POST['complete_lesson_id']);

    // Перевіряємо, чи вже завершено
    $stmt = $pdo->prepare("SELECT * FROM user_lessons_progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        // Отримуємо XP уроку
        $stmt = $pdo->prepare("SELECT xp_reward FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch();

        if ($lesson) {
            $xp = $lesson['xp_reward'];

            // Додаємо запис про завершення уроку
            $stmt = $pdo->prepare("INSERT INTO user_lessons_progress (user_id, lesson_id, is_completed, completed_at) VALUES (?, ?, 1, NOW())");
            $stmt->execute([$user_id, $lesson_id]);

            // Додаємо XP
            $stmt = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
            $stmt->execute([$xp, $user_id]);
        }
    }

    header("Location: lessons.php");
    exit();
}

// Отримання всіх уроків з прогресом
$stmt = $pdo->prepare("SELECT l.*, ulp.is_completed 
                       FROM lessons l
                       LEFT JOIN user_lessons_progress ulp ON l.id = ulp.lesson_id AND ulp.user_id = ?");
$stmt->execute([$user_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Уроки з фінансової грамотності</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <style>
        /* СТИЛІ ЗАЛИШАЮ ТАКІ, ЯК ТИ НАДАВ — НЕ ЗМІНЮЮ ЖОДНОГО РЯДКА */

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
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background: #1a1a1a;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 0 40px rgba(255, 255, 0, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 28px;
            color: #f1f1f1;
            text-shadow: 0 0 5px #ff0;
        }

        .lesson-card {
            background: #111;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.05);
            border: 1px solid #333;
            transition: all 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 0 15px rgba(255, 255, 0, 0.2);
        }

        .lesson-title {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 12px;
            text-shadow: 0 0 3px #ff0;
        }

        .lesson-info {
            font-size: 15px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .completed {
            color: #8fff8f;
            font-weight: bold;
        }

        .not-completed {
            color: #ff8f8f;
            font-weight: bold;
        }

        .view-btn {
            background: #111;
            color: #ff0;
            border: 2px solid #ff0;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            text-shadow: 0 0 4px #ff0;
            transition: 0.3s ease;
            font-size: 16px;
            margin-right: 10px;
        }

        .view-btn:hover {
            background: #222;
            box-shadow: 0 0 12px rgba(255, 255, 0, 0.4);
        }

        .lesson-content {
            margin-top: 15px;
            font-size: 15px;
            line-height: 1.6;
            color: #eee;
            display: none;
        }

        .complete-btn {
            background: #111;
            color: #8fff8f;
            border: 2px solid #8fff8f;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 16px;
        }

        .complete-btn:hover {
            background: #222;
            box-shadow: 0 0 12px rgba(143, 255, 143, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px 20px;
            }

            .view-btn, .complete-btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
    <script>
        function toggleContent(id) {
            const content = document.getElementById('content-' + id);
            content.style.display = content.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>
<body>
<div class="container">
    <div class="header">
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none;">
        <button style="padding: 10px 18px; border-radius: 12px; background-color: #111; color: #ff0; border: 2px solid #ff0; font-size: 15px; cursor: pointer; transition: 0.3s ease;">
            ⬅ Повернутись на головну
        </button>
    </a>
        <h2>📚 Уроки з фінансової грамотності</h2>
    </div>

    <?php foreach ($lessons as $lesson): ?>
        <div class="lesson-card">
            <div class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></div>
            <div class="lesson-info">
                ⏱ Час: <?= $lesson['estimated_time'] ?> хв |
                🌟 XP: <?= $lesson['xp_reward'] ?> |
                <?= $lesson['is_completed'] ? '<span class="completed">✅ Завершено</span>' : '<span class="not-completed">❌ Не завершено</span>' ?>
            </div>

            <button class="view-btn" onclick="toggleContent(<?= $lesson['id'] ?>)">Переглянути зміст</button>

            <?php if (!$lesson['is_completed']): ?>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="complete_lesson_id" value="<?= $lesson['id'] ?>">
                    <button class="complete-btn" type="submit">Завершити урок</button>
                </form>
            <?php endif; ?>

            <div class="lesson-content" id="content-<?= $lesson['id'] ?>">
                <?= nl2br(htmlspecialchars($lesson['content'])) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
