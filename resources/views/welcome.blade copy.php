<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Price Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
    <h1>Crypto Price Chart</h1>
    <canvas id="cryptoChart" width="400" height="200"></canvas>

    <?php
    // ฟังก์ชันดึงข้อมูลจาก CoinGecko API
    function getCryptoData($cryptoId) {
        $url = "https://api.coingecko.com/api/v3/coins/$cryptoId/market_chart?vs_currency=usd&days=15";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // ดึงข้อมูล Bitcoin จาก CoinGecko API
    $cryptoId = "bitcoin";
    $data = getCryptoData($cryptoId);

    if (!empty($data)) {
        $prices = $data['prices'];
        $times = [];
        $values = [];

        foreach ($prices as $price) {
            $times[] = date("Y-m-d H:i:s", $price[0] / 1000);
            $values[] = $price[1];
        }
    } else {
        echo "<p>Unable to fetch data.</p>";
    }
    ?>

    <script>
        var ctx = document.getElementById('cryptoChart').getContext('2d');
        var cryptoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($times); ?>,
                datasets: [{
                    label: 'Price (USD)',
                    data: <?php echo json_encode($values); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            tooltipFormat: 'PPpp'
                        },
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Price (USD)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
