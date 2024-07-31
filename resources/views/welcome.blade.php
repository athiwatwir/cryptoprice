<?php
// ฟังก์ชันดึงข้อมูลจาก CoinGecko API
function getCryptoData($cryptoId) {
    $url = "https://api.coingecko.com/api/v3/coins/$cryptoId/market_chart?vs_currency=usd&days=10";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// ฟังก์ชันวาดกราฟ
function drawChart($data, $width = 800, $height = 600) {
    $image = imagecreatetruecolor($width, $height);

    // กำหนดสี
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $red = imagecolorallocate($image, 255, 0, 0);

    // เติมพื้นหลังด้วยสีขาว
    imagefill($image, 0, 0, $white);

    // วาดกรอบ
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $black);

    $prices = $data['prices'];
    $num_points = count($prices);
    $max_price = max(array_column($prices, 1));
    $min_price = min(array_column($prices, 1));

    $padding = 50;
    $graph_width = $width - 2 * $padding;
    $graph_height = $height - 2 * $padding;

    // วาดเส้นกราฟ
    for ($i = 1; $i < $num_points; $i++) {
        $x1 = $padding + (($i - 1) / ($num_points - 1)) * $graph_width;
        $y1 = $height - $padding - (($prices[$i - 1][1] - $min_price) / ($max_price - $min_price)) * $graph_height;
        $x2 = $padding + ($i / ($num_points - 1)) * $graph_width;
        $y2 = $height - $padding - (($prices[$i][1] - $min_price) / ($max_price - $min_price)) * $graph_height;

        imageline($image, $x1, $y1, $x2, $y2, $red);
    }

    // สร้างไฟล์ JPG
    $filename = 'crypto_chart.jpg';
    imagejpeg($image, $filename);

    // ล้างหน่วยความจำ
    imagedestroy($image);

    return $filename;
}

// ดึงข้อมูล Bitcoin จาก CoinGecko API
$cryptoId = "bitcoin";
$data = getCryptoData($cryptoId);

if (!empty($data)) {
    $filename = drawChart($data);
    echo "<h2>Chart created successfully: <a href='$filename' download>Download JPG</a></h2>";
} else {
    echo "<p>Unable to fetch data.</p>";
}
?>
