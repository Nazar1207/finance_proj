<?php
$host = 'localhost';
$dbname = 'finance_app';
$username = 'root';
$password = ''; // або твій пароль, якщо є

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Помилка підключення до БД: " . $e->getMessage());
}
?>