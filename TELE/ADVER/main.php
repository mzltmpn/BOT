<?php

// Delete cookie file before starting to ensure fresh session
date_default_timezone_set('Asia/Jakarta');
function countdown($total) {
    for ($i = $total; $i > 0; $i--) {
        // buat string countdown
        $line = "[ ⏳ ] Tunggu $i detik...";
        // tulis ulang dengan padding supaya overwrite sisa karakter lama
        echo "\r" . str_pad($line, 40, " ");
        flush();
        sleep(1);
    }
    echo "\r[ ✅ ] Waktu tunggu selesai!            ";
    echo "\r                                        \r";
   
}


function sendTelegram($message) {
    $botToken = "8474981251:AAEqCIJPF6dOfFnZ6ScZdCmmGOfz-6QaKbw";
    $chatId = "5548621274";
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}


function post($url,$payload){
$base = 'https://tgmineapp.live';
$url  = $base . $url;
$headers = [
        'Host: tgmineapp.live',
        'Accept: */*',
        'Accept-Language: en-US,en;q=0.9',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'Referer: https://tgmineapp.live/ton/index.html',
        'Sec-CH-UA: "Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
        'Sec-CH-UA-Platform: "Windows"',
        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty',
        'Sec-GPC: 1',
        'Priority: u=1, i'
    ];


$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // To get headers in response
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);


$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

return $body;
}


function get($url){
$base = 'https://tgmineapp.live';
$url  = $base . $url;

    // --- Header persis seperti browser ---
$headers = [
        'Host: tgmineapp.live',
        'Accept: */*',
        'Accept-Language: en-US,en;q=0.9',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'Referer: https://tgmineapp.live/ton/index.html',
        'Sec-CH-UA: "Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
        'Sec-CH-UA-Platform: "Windows"',
        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty',
        'Sec-GPC: 1',
        'Priority: u=1, i'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_ENCODING        => '',           // biar otomatis handle gzip, br, zstd
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_FOLLOWLOCATION  => true,         // ikuti redirect
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0 // pakai HTTP/2
    ]);

$response = curl_exec($ch);
curl_close($ch);

return $response;
}


$data = file_get_contents('user.json');
$user = json_decode($data, true);
$telegram_id = $user['user_id'];
$response = get('/ton/api/history.php?user_id='.$telegram_id);
$json = json_decode($response, true);
$username = $json['user']['username'];
$points = $json['user']['points'];
$log = "😎 Login  : $username\n💵 Points : $points\n";
echo $log;
echo str_repeat("=", 40) . "\n";
while (true) {

$res = post('/ton/api/watch.php', json_encode(['user_id' => $telegram_id]));
countdown(5);
$res = post('/ton/api/spin.php', json_encode(['user_id' => $telegram_id]));
$json = json_decode($res, true);
$res = $json['reward'];
echo "🎡 Spin Result : $res Point\n";
$response = get('/ton/api/history.php?user_id='.$telegram_id);
$json = json_decode($response, true);
$points = $json['user']['points'];
$daily = $json['user']['daily_count'];
echo "💵 Update Points : $points\n";
echo "🔄 Daily Count : $daily\n";

$msg = "🟢  <b>ADVERA CLAIM</b> 🟢\n\n";
$msg .= "🆔 Bot : @AdverraTonBot\n";
$msg .= "🎡 Spin Result : $res Point\n";
$msg .= "💵 Update Points: {$points}\n";
$msg .= "🔄 Daily Count: {$daily}\n";
$msg .= "📅 " . date('Y-m-d H:i:s') . "\n";
sendTelegram($msg);
echo str_repeat("=", 40) . "\n";
if ($daily == 50) {
    echo "[ ⚠️ ] DAILY LIMIT REACHED. WAITING FOR RESET...\n";
    
$msg = "🔴 <b>ADVERA CLAIM ALERT</b> 🔴\n\n";
$msg .= "🆔 Bot : @AdverraTonBot\n";
$msg .= "💵 Update Points: {$points}\n";
$msg .= "⚠️ DAILY LIMIT REACHED. \n";
$msg .= "📅 " . date('Y-m-d H:i:s') . "\n";
$msg .= "\n mazlana \n";
sendTelegram($msg);
    break;
}else{
    countdown(5);
}
}