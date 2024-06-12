<?php
$secretKey = $_ENV['archivekey'];

if (!file_exists('started.txt')) {
    echo "Error: started.php has not been executed. \nRunning started.php...\n";
    exec('php started.php', $output, $return_var);

    if ($return_var === 0) {
        echo 'started.php ran successfully!\n';
        print_r($output);
    } else {
        echo 'There was an error running started.php';
    }

    exit;
}

$directory = '/var/www/html/server/groups/';

$additionalFiles = [
    '/var/www/html/server/groups.txt',
    '/var/www/html/server/users.txt'
];

$zip = new ZipArchive();
$zipFileName = 'groups.zip';

if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($directory));
            $zip->addFile($filePath, 'groups/' . $relativePath);
        }
    }

    foreach ($additionalFiles as $file) {
        $zip->addFile($file, basename($file));
    }

    $zip->close();

    $serverUrl = 'http://atypicalpotato.heliohost.us/receive-zip.php';
    $fileField = 'zip_file';

    $postData = [
        $fileField => new CURLFile($zipFileName),
        'key' => $secretKey
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $serverUrl);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        echo 'Error sending ZIP file to server: ' . $curlError;
    } else {
        echo 'ZIP file sent successfully to server!';
    }

    unlink($zipFileName);

} else {
    echo 'Failed to create zip file';
}
?>
