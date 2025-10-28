<?php
date_default_timezone_set('Asia/Jakarta');

/* =============== 🔹 UTILITAS =============== */
function countdown($t){
    for($i=$t;$i>0;$i--){
        echo "\r⏳ Tunggu $i detik... "; flush(); sleep(1);
    }
    echo "\r✅ Waktu tunggu selesai!\n";
}

function cookieLabel($f) {
    if ($f === 'mazlanadata.txt') {
        return 'mazlanadata';
    } elseif ($f === 'botcwt.txt') {
        return 'botcwt';
    } elseif ($f === 'dayanaji533.txt') {
        return 'dayanaji533';
    } elseif ($f === 'mazlanaproject.txt') {
        return 'mazlanaproject';
    } else {
        return 'None ?';
    }
}


function sendTelegram($msg){
    $bot="8474981251:AAEqCIJPF6dOfFnZ6ScZdCmmGOfz-6QaKbw";
    $chat="5548621274";
    $url="https://api.telegram.org/bot{$bot}/sendMessage";
    $data=['chat_id'=>$chat,'text'=>$msg,'parse_mode'=>'HTML'];
    $c=curl_init($url);
    curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,
        CURLOPT_POSTFIELDS=>json_encode($data),
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>0]);
    curl_exec($c); curl_close($c);
}

function curl_common_headers($ck){
    return [
        "User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36",
        "Accept: application/json, text/plain, */*",
        "Origin: https://luckywatch.pro",
        "Referer: https://luckywatch.pro/",
        "Cookie: $ck"
    ];
}

function post($url,$data,$cookieFile){
    $base="https://luckywatch.pro";
    $path=__DIR__."/$cookieFile";
    $ck=trim(@file_get_contents($path));
    $ch=curl_init($base.$url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_HTTPHEADER=>curl_common_headers($ck),
        CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data,
        CURLOPT_COOKIEFILE=>$path,
        CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>0,
        CURLOPT_TIMEOUT=>30]);
    $r=curl_exec($ch); curl_close($ch);
    return $r;
}

/* =============== 🔹 SOLVER CEPAT =============== */
function decodeImage($b){ if(strpos($b,",")!==false)$b=explode(",",$b,2)[1]; return @imagecreatefromstring(base64_decode($b)); }

function solve($data){
    $key='UYjkiAKHYyzbVvg90D3TlW83Q5cyIlOe';
    if(!isset($data['data']['image']))return null;
    $imgMain=decodeImage($data['data']['image']);
    $q=$data['data']['queue'];
    $imgQueue=is_array($q)?decodeImage(implode('',$q)):decodeImage($q);
    if(!$imgMain)return null;

    $w=imagesx($imgMain);$h=imagesy($imgMain);$bar=intval($h*0.25);
    $canvas=imagecreatetruecolor($w,$h+$bar);
    imagesavealpha($canvas,true);
    $tr=imagecolorallocatealpha($canvas,0,0,0,127);
    imagefill($canvas,0,0,$tr);
    imagecopy($canvas,$imgMain,0,0,0,0,$w,$h);
    $white=imagecolorallocate($canvas,255,255,255);
    imagefilledrectangle($canvas,0,$h,$w,$h+$bar,$white);
    if($imgQueue){
        $qw=imagesx($imgQueue);$qh=imagesy($imgQueue);
        $tH=$bar*0.8;$tW=intval($qw*($tH/$qh));
        $x=intval(($w-$tW)/2);$y=intval($h+($bar-$tH)/2);
        imagecopyresampled($canvas,$imgQueue,$x,$y,0,0,$tW,$tH,$qw,$qh);
    }
    ob_start(); imagepng($canvas); $png=ob_get_clean();
    imagedestroy($canvas); imagedestroy($imgMain); if($imgQueue) imagedestroy($imgQueue);
    $b64=base64_encode($png);

    $api="https://api.sctg.xyz/in.php";
    $p=http_build_query(['key'=>$key,'method'=>'workcash','body'=>$b64]);
    $ch=curl_init($api);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$p,
        CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>0]);
    $res=curl_exec($ch); curl_close($ch);

    if(!preg_match('/\|(\d+)/',$res,$m))return null;
    $id=$m[1]; echo"🧠 Solver ID: $id\n";

    $resApi="https://api.sctg.xyz/res.php?key=$key&id=$id&action=get";
    $ctx=stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
    $r=@file_get_contents($resApi,false,$ctx);
    if($r && stripos($r,'OK|Coordinate:')===0)
        return trim(substr($r,strpos($r,':')+1));
    return null;
}

/* =============== 🔹 MAIN FLOW =============== */
function runCookie($cookieFile){
    $label=cookieLabel($cookieFile);
    echo "💌 Login As : $label\n";

    while(true){
        $r=post("/api/user/tasks/","method=get&mac=0",$cookieFile);
        $j=json_decode($r,true);

        if(!($j && $j['status']==='ok')){
            echo "❌ Tidak ada task untuk $label\n";
            $msg = "🔴 <b>LUCKY WATCH - ALERT</b> 🔴\n\n";
            $msg .= "🌐 WEB : https://luckywatch.pro\n";
            $msg .= "💌 Login As : $label\n";
            $msg .= "❌ Iklan Habis .\n";
            $msg .= "📅 " . date('Y-m-d H:i:s');
            sendTelegram($msg);
            return false; // keluar dan lanjut cookie berikut
        }

        $t=$j['data'];
        echo "🆔 ID: {$t['id']} | 💰 Balance: {$t['balance']}\n";
        $finger=http_build_query(['TaskId'=>$t['id'],'fin'=>['platform'=>'Linux armv81','dpr'=>2.7]]);
        post("/api/user/tasks/start/",$finger,$cookieFile);
        countdown($t['duration']);

        echo "📡 Mengecek status task ($label)...\n";
        $r=post("/api/user/captcha/check/","refreshTask=0",$cookieFile);
        $j=json_decode($r,true);

        if($j && isset($j['data']['image'])){
            echo "🧩 CAPTCHA terdeteksi ($label)...\n";
            $solve=solve($j);
            if(!$solve){ echo "❌ Solver gagal ($label)\n"; continue; }

            $coords=explode(';',$solve); $arr=[];
            foreach($coords as $p)
                if(preg_match('/x=(\d+),y=(\d+)/',$p,$m))
                    $arr[]=['x'=>(int)$m[1],'y'=>(int)$m[2]];

            $enc=http_build_query(['coor'=>$arr]);
            $r=post("/api/user/captcha/check/",$enc,$cookieFile);
            $json=json_decode($r,true);
            if(($json['status']??'')=="ok"){
                $rew=$json['data']['reward'];
                echo "✅ CAPTCHA SOLVED ($label) 💰+$rew\n";
                 $msg = "🟢 <b>LUCKY WATCH - CAPTCHA SOLVED</b> 🟢\n\n";
                 $msg .= "🌐 WEB : https://luckywatch.pro\n";
                 $msg .= "💌 Login As : $label\n";
                 $msg .= "💰 Reward: $rew\n";
                 $msg .= "💵 Balance: {$t['balance']}\n";
                 $msg .= "📅 " . date('Y-m-d H:i:s');
                 sendTelegram($msg);
            } else {
                echo "❌ CAPTCHA FAIL ($label)\n";
                continue;
            }
        } else {
            $json=json_decode($r,true);
            $rew=$json['data']['reward']??"(unknown)";
            echo "✅ Task complete ($label) 💰+$rew\n";
            $msg = "🟢 <b>LUCKY WATCH - TASK COMPLETE</b> 🟢\n\n";
            $msg .= "🌐 WEB : https://luckywatch.pro\n";
            $msg .= "💌 Login As : $label\n";
            $msg .= "💰 Reward: $rew\n";
            $msg .= "💵 Balance: {$t['balance']}\n";
            $msg .= "📅 " . date('Y-m-d H:i:s');
            sendTelegram($msg);
           
        }

        echo "--------------------------------------\n";
        // lanjut langsung ke task berikut di cookie yang sama
    }
}

/* =============== 🔹 EKSEKUSI =============== */
if(!runCookie('mazlanadata.txt')){
    if(!runCookie('botcwt.txt')){
        runCookie('dayanaji533.txt');
        if(!runCookie('dayanaji533.txt')){
        runCookie('mazlanaproject.txt');
    }
    }
}


echo "\n🚫 Semua cookie tidak ada task.\n";
sendTelegram("🚫 Semua cookie (💌 A & 💌 B) tidak ada task.");
