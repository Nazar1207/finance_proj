<?php
// Функція, яку ми тестуємо
function calculateBalance($transactions) {
    $balance = 0;
    foreach ($transactions as $t) {
        if ($t['type'] === 'income') {
            $balance += $t['amount'];
        } elseif ($t['type'] === 'expense') {
            $balance -= $t['amount'];
        }
    }
    return $balance;
}

// Тестовий скрипт
function testCalculateBalance() {
    $testData = [
        ['type' => 'income', 'amount' => 1000],
        ['type' => 'expense', 'amount' => 300],
        ['type' => 'income', 'amount' => 200],
        ['type' => 'expense', 'amount' => 100],
    ];

    $expected = 800;
    $result = calculateBalance($testData);

    if ($result === $expected) {
        echo "✅ Test passed. Balance: $result\n";
    } else {
        echo "❌ Test failed. Expected: $expected, Got: $result\n";
    }
}

testCalculateBalance();
?>
