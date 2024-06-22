<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');

foreach ($_GET as $key => $value) {
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $cleanValue = str_replace(['|', '/', '\\'], '', $escapedValue);
    $_GET[$key] = $cleanValue;
}

if (isset($_GET["val"]) && $_GET["val"] == "rm" && isset($_GET["group"]) && isset($_GET["admin_user"]) && isset($_GET["admin_password"]) && isset($_GET["target_user"]) && isset($_GET["text"]) && isset($_GET["gp"])) {

    $group = $_GET["group"];
    $adminUser = $_GET["admin_user"];
    $adminPassword = $_GET["admin_password"];
    $targetUser = $_GET["target_user"];
    $text = $_GET["text"];
    $grouppassword = $_GET["gp"];
    if ($grouppassword == "") {
        $grouppassword = "none";
    }

    $groupEncrypted = openssl_encrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $grouppasswordEncrypted = openssl_encrypt($grouppassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

    if (strpos(file_get_contents("groups.txt"), ($groupEncrypted . ":" . $grouppasswordEncrypted)) !== false) {
        
        $adminUserEncrypted = openssl_encrypt($adminUser, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $adminPasswordEncrypted = openssl_encrypt($adminPassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents("users.txt"), ($adminUserEncrypted . ":" . $adminPasswordEncrypted)) !== false) {

            if (strpos(file_get_contents("admins.txt"), $adminUser . "|") !== false) {
                
                $filename = "groups/" . $group . ".txt";
                if (file_exists($filename)) {
                    $fileContents = file_get_contents($filename);
                    $messages = explode("|", $fileContents);
                    $newMessages = [];
                    $messageFound = false;

                    $targetUserEncrypted = openssl_encrypt($targetUser, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
                    $textEncrypted = openssl_encrypt($text, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

                    for ($i = count($messages) - 1; $i >= 0; $i--) {
                        if ($messages[$i] != "") {
                            $parts = explode(":", $messages[$i]);
                            if (count($parts) == 4 && $parts[2] == $targetUserEncrypted && $parts[3] == $textEncrypted) {
                                $messageFound = true;
                                continue;
                            }
                            $newMessages[] = $messages[$i];
                        }
                    }

                    $newMessages = array_reverse($newMessages);

                    if ($messageFound) {
                        $newFileContents = implode("|", $newMessages);
                        file_put_contents($filename, $newFileContents);
                        echo "Message removed successfully.";
                    } else {
                        echo "Message not found.";
                    }
                } else {
                    echo "Group file not found.";
                }
            } else {
                echo "Admin user is not an admin.";
            }
        } else {
            echo "Invalid admin user or password.";
        }
    } else {
        echo "Invalid group or password.";
    }
} else {
    echo "Not a valid header.";
}
?>
