<?php
// Путь к файлу данных вне public_html
$jsonFile = '/home/a1329257/len77_data/status.json';

// Проверка существования файла
if (!file_exists($jsonFile)) {
    http_response_code(500);
    die('Ошибка: файл данных не найден');
}

// Чтение и декодирование JSON
$json = file_get_contents($jsonFile);
$data = json_decode($json, true);

// Проверка корректности JSON
if (!$data || !isset($data['apartments'])) {
    http_response_code(500);
    die('Ошибка: некорректный формат данных');
}

// Расчет статистики
$totalTarget = 0;
$totalCars = 0;
foreach ($data['apartments'] as $apt) {
    if (!empty($apt['target'])) $totalTarget += 10000;
    $totalCars += intval($apt['cars']);
}
$reserve = $totalTarget - intval($data['plan']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статус платежей | Ленинский 77к2</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; background-color: #f4f7f6; font-size: 16px; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-height: 100vh; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #7f8c8d; text-decoration: none; font-size: 15px; font-weight: 500; }
        .back-link:hover { color: #27ae60; }
        h1 { font-size: 22px; color: #1a252f; margin-bottom: 5px; text-align: center; line-height: 1.3; }
        .subtitle { text-align: center; color: #27ae60; font-size: 15px; font-weight: bold; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 15px; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: bold; color: #555; background-color: #f9f9f9; text-align: center; }
        th:first-child { text-align: left; padding-left: 15px; }
        td:first-child { font-weight: bold; color: #1a252f; padding-left: 15px; }
        td.mark { text-align: center; font-size: 18px; color: #27ae60; letter-spacing: 2px; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .summary-card { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 10px; padding: 15px; margin-bottom: 25px; }
        .summary-title { font-size: 16px; font-weight: bold; color: #1a252f; text-align: center; margin-bottom: 12px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #ddd; font-size: 15px; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row span { color: #555; }
        .summary-row b { font-size: 16px; }
        .collected { color: #27ae60; }
        .plan { color: #1a252f; }
        .reserve-neg { color: #c62828; }
        .reserve-pos { color: #27ae60; }
        .reserve-note { font-size: 12px; color: #7f8c8d; text-align: center; margin-top: 10px; font-style: italic; }
        .note { font-size: 13px; color: #7f8c8d; text-align: center; margin-top: 20px; }
        @media print {
            body { background: #fff; }
            .container { box-shadow: none; padding: 0; max-width: 100%; }
            .back-link { display: none; }
            th { background-color: #fff; border-bottom: 2px solid #000; }
            td { border-bottom: 1px solid #ccc; }
            td.mark { color: #000; }
            .summary-card { border: 1px solid #000; background: #fff; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.html" class="back-link">← На главную</a>
    <h1>Статус платежей по шлагбауму</h1>
    <div class="subtitle">по состоянию на <?= htmlspecialchars($data['updated']) ?></div>

    <table>
        <thead>
            <tr><th>Кв.</th><th>Целевой</th><th>Регулярный</th></tr>
        </thead>
        <tbody>
            <?php foreach ($data['apartments'] as $apt): ?>
            <tr>
                <td><?= htmlspecialchars($apt['n']) ?></td>
                <td class="mark"><?= !empty($apt['target']) ? '✅' : '' ?></td>
                <td class="mark"><?= 
                    isset($apt['cars']) && $apt['cars'] == 2 ? '✅✅' : 
                    (isset($apt['cars']) && $apt['cars'] == 1 ? '✅' : '') 
                ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-card">
        <div class="summary-title">📊 Статистика по целевому сбору</div>
        <div class="summary-row"><span>Итого собрано:</span><b class="collected"><?= number_format($totalTarget, 0, ' ', ' ') ?> ₽</b></div>
        <div class="summary-row"><span>План по смете:</span><b class="plan"><?= number_format(intval($data['plan']), 0, ' ', ' ') ?> ₽</b></div>
        <div class="summary-row"><span>Резерв:</span><b class="<?= $reserve < 0 ? 'reserve-neg' : 'reserve-pos' ?>"><?= number_format($reserve, 0, ' ', ' ') ?> ₽</b></div>
        <div class="reserve-note">* Резерв станет положительным, когда сумма сборов превысит план.</div>
    </div>

    <div class="note">
        * Целевой взнос — 10 000 ₽ с квартиры.<br>
        ** Регулярный взнос — 4 000 ₽ с автомобиля (✅✅ = оплачено за два авто).
    </div>
</div>
</body>
</html>
