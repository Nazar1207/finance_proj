<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];
$lesson_id = $_POST['lesson_id'];

// Перевірка, чи урок уже завершено
$stmt = $conn->prepare("SELECT completed FROM user_lessons WHERE user_id = ? AND lesson_id = ?");
$stmt->bind_param("ii", $user_id, $lesson_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && !$row['completed']) {
    // Позначити як завершений
    $stmt = $conn->prepare("UPDATE user_lessons SET completed = 1 WHERE user_id = ? AND lesson_id = ?");
    $stmt->bind_param("ii", $user_id, $lesson_id);
    $stmt->execute();

    // Отримати XP за урок
    $stmt = $conn->prepare("SELECT xp FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
    $xp = $lesson['xp'];

    // Додати XP користувачу
    $stmt = $conn->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
    $stmt->bind_param("ii", $xp, $user_id);
    $stmt->execute();

    echo "Урок завершено! Отримано $xp XP.";
} else {
    echo "Урок уже завершено або не знайдено.";
}
?>
