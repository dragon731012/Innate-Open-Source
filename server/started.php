<?php
if (file_exists('started.txt')) {
    echo "Error: started.php has already been executed.";
    exit;
}

function pingServer($url, $key) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['key' => $key]));

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = curl_error($ch);
        echo "Error: $error";
    } else {
        echo "Response from server: $response";
    }

    curl_close($ch);
}

$url = 'http://atypicalpotato.heliohost.us/started.php';
$key = $_ENV['archivekey'];

pingServer($url, $key);
?>
