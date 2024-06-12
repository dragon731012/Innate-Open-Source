<?php
$secretKey = $_ENV['archivekey'];

if (file_exists('started.txt')) {
    echo "Error: started.php has already been executed.";
    exit;
}

if (isset($_POST['key']) && $_POST['key'] === $secretKey) {
    $zipFile = $_FILES['zip_file'];

    // Check for errors
    if ($zipFile['error'] !== UPLOAD_ERR_OK) {
        echo 'Error uploading file: ' . $zipFile['error'];
        exit;
    }

    // Get the directory of the current script
    $uploadDirectory = __DIR__ . '/';

    // Move the uploaded file to the specified directory
    $destination = $uploadDirectory . basename($zipFile['name']);
    if (move_uploaded_file($zipFile['tmp_name'], $destination)) {
        echo 'File uploaded successfully!';

        // Extract the ZIP file
        $zip = new ZipArchive;
        if ($zip->open($destination) === TRUE) {
            $zip->extractTo($uploadDirectory);
            $zip->close();
            echo 'File extracted successfully!';
            file_put_contents('started.txt', 'Started');
        } else {
            echo 'Error extracting the ZIP file!';
        }

        // Optionally, you can delete the ZIP file after extraction
        // unlink($destination);
    } else {
        echo 'Error moving file to destination!';
    }
}
?>
