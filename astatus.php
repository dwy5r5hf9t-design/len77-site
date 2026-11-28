<?php
$jsonFile = '/home/a1329257/len77_data/status.json';
if (!file_exists($jsonFile)) { http_response_code(500); die('Ошибка: файл данных не найден'); }
$json = file_get_contents($jsonFile);
$data = json_decode($json, true);
if (!$data || !isset($data['apartments'])) { http_response_code(500); die('Ошибка: некорректный формат данных'); }

// === РАСЧЁТЫ ИЗ JSON ===
$priceTarget = intval($data['prices']['target'] ?? 10000);
$priceCar    = intval($data['prices']['car'] ?? 4000);

$aptCount    = 0;
$carCount    = 0;
foreach ($data['apartments'] as $apt) {
    if (!empty($apt['target'])) $aptCount++;
    $carCount += intval($apt['cars'] ?? 0);
}
$sumTarget = $aptCount * $priceTarget;
$sumCars   = $carCount * $priceCar;
$sumIncome = $sumTarget + $sumCars;

// Затраты
$expenses = $data['expenses'] ?? [];
$sumExpense = 0;
foreach ($expenses as $e) $sumExpense += intval($e['amount'] ?? 0);

$reserve = $sumIncome - $sumExpense;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Баланс по шлагбауму | Ленинский 77к2</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; background-color: #faeaea; font-size: 16px; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-height: 100vh; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #7f8c8d; text-decoration: none; font-size: 15px; font-weight: 500; }
        .back-link:hover { color: #27ae60; }
        h1 { font-size: 22px; color: #1a252f; margin-bottom: 5px; text-align: center; line-height: 1.3; }
        .subtitle { text-align: center; color: #27ae60; font-size: 15px; font-weight: bold; margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; color: #1a252f; margin: 25px 0 12px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 15px; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: bold; color: #555; background-color: #f9f9f9; text-align: center; font-size: 14px; }
        th:first-child { text-align: left; padding-left: 12px; }
        td:first-child { font-weight: bold; color: #1a252f; padding-left: 12px; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        td.center { text-align: center; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .total-row { background-color: #f4fbf7; font-weight: bold; }
        .total-row.expense { background-color: #fff5f5; }
        .reserve-block { background: #f4fbf7; border: 2px solid #27ae60; border-radius: 10px; padding: 20px; margin: 25px 0; text-align: center; }
        .reserve-label { font-size: 20px; font-weight: bold; color: #1a252f; margin-bottom: 5px; }
        .reserve-note { font-size: 13px; color: #7f8c8d; margin-bottom: 12px; font-style: italic; }
        .reserve-value { font-size: 28px; font-weight: bold; color: #27ae60; }
        .reserve-value.neg { color: #c62828; }
        .plan-tag { color: #7f8c8d; font-size: 12px; font-weight: normal; }
        .apt-table { margin-top: 30px; }
        td.mark { text-align: center; font-size: 18px; color: #27ae60; letter-spacing: 2px; }
        .note { font-size: 13px; color: #7f8c8d; text-align: center; margin-top: 20px; }
        @media print {
            body { background: #fff; }
            .container { box-shadow: none; padding: 0; max-width: 100%; }
            .back-link { display: none; }
            th { background-color: #fff; border-bottom: 2px solid #000; }
            td { border-bottom: 1px solid #ccc; }
            td.mark { color: #000; }
            .reserve-block { border: 2px solid #000; background: #fff; }
            .reserve-value { color: #000; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.html" class="back-link">← На главную</a>
    <h1>Баланс по шлагбауму</h1>
    <div class="subtitle">актуализировано <?= htmlspecialchars($data['updated']) ?></div>

    <div class="section-title">📊 Статистика поступлений</div>
    <table>
        <thead><tr><th>Сбор</th><th>Размер</th><th>Кол-во</th><th>Сумма</th></tr></thead>
        <tbody>
            <tr>
                <td>Целевой</td>
                <td class="num"><?= number_format($priceTarget, 0, ' ', ' ') ?> ₽</td>
                <td class="center"><?= $aptCount ?> квартир</td>
                <td class="num"><b><?= number_format($sumTarget, 0, ' ', ' ') ?> ₽</b></td>
            </tr>
            <tr>
                <td>Регулярный 2026</td>
                <td class="num"><?= number_format($priceCar, 0, ' ', ' ') ?> ₽</td>
                <td class="center"><?= $carCount ?> авто</td>
                <td class="num"><b><?= number_format($sumCars, 0, ' ', ' ') ?> ₽</b></td>
            </tr>
            <tr class="total-row">
                <td>Итого</td><td></td><td></td>
                <td class="num" style="color: #27ae60; font-size: 16px;"><?= number_format($sumIncome, 0, ' ', ' ') ?> ₽</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">📊 Статистика затрат</div>
    <table>
        <thead><tr><th>Платёж</th><th>Дата</th><th>Сумма</th></tr></thead>
        <tbody>
            <?php foreach ($expenses as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['name']) ?></td>
                <td class="center">
                    <?= htmlspecialchars($e['date']) ?>
                    <?php if (!empty($e['planned'])): ?><span class="plan-tag">(план)</span><?php endif; ?>
                </td>
                <td class="num"><?= number_format(intval($e['amount']), 0, ' ', ' ') ?> ₽</td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row expense">
                <td>Итого</td><td></td>
                <td class="num" style="color: #c62828; font-size: 16px;"><?= number_format($sumExpense, 0, ' ', ' ') ?> ₽</td>
            </tr>
        </tbody>
    </table>

    <div class="reserve-block">
        <div class="reserve-label">В резерве</div>
        <div class="reserve-note">на случай повреждений и дополнительного обслуживания</div>
        <div class="reserve-value <?= $reserve < 0 ? 'neg' : '' ?>">
            <?= ($reserve < 0 ? '−' : '') . number_format(abs($reserve), 0, ' ', ' ') ?> ₽
        </div>
    </div>

    <div class="section-title apt-table">📋 Статус по квартирам</div>
    <table class="apt-table">
        <thead><tr><th>Кв.</th><th>Целевой</th><th>Регулярный</th></tr></thead>
        <tbody>
            <?php foreach ($data['apartments'] as $apt): ?>
            <tr>
                <td><?= htmlspecialchars($apt['n']) ?></td>
                <td class="mark"><?= !empty($apt['target']) ? '✅' : '' ?></td>
                <td class="mark"><?= str_repeat('✅', intval($apt['cars'] ?? 0)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="note">
        * Целевой взнос — <?= number_format($priceTarget, 0, ' ', ' ') ?> ₽ с квартиры.<br>
        ** Регулярный взнос — <?= number_format($priceCar, 0, ' ', ' ') ?> ₽ с автомобиля (каждая ✅ = одно авто).
    </div>
</div>
</body>
</html>
