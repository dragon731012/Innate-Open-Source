<?php

if (isset($_GET["val"]) && $_GET["val"] == "rm" && isset($_GET["group"]) && isset($_GET["admin_user"]) && isset($_GET["admin_password"]) && isset($_GET["target_user"]) && isset($_GET["text"]) && isset($_GET["gp"])) {

    $group = $_GET["group"];
    $adminUser = $_GET["admin_user"];
    $adminPassword = $_GET["admin_password"];
    $targetUser = $_GET["target_user"];
    $text = $_GET["text"];
    $grouppassword = $_GET["gp"];
    if ($grouppassword==""){
        $grouppassword="none";
    }

    $groupEncrypted = openssl_encrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $grouppasswordEncrypted = openssl_encrypt($grouppassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

    if (strpos(file_get_contents("groups.txt"), ($groupEncrypted . ":" . $grouppasswordEncrypted)) !== false) {
        
        $adminUserEncrypted = openssl_encrypt($adminUser, 'AES-256-CBC', $_ENV['archivekey'], 0, $_ENV['iv']);
        $adminPasswordEncrypted = openssl_encrypt($adminPassword, 'AES-256-CBC', $_ENV['archivekey'], 0, $_ENV['iv']);

        if (strpos(file_get_contents("users.txt"), ($adminUserEncrypted . ":" . $adminPasswordEncrypted)) !== false) {

            if (strpos(file_get_contents("admins.txt"), $adminUser . "|") !== false) {
                
                $filename = "groups/" . $group . ".txt";
                if (file_exists($filename)) {
                    file_put_contents($filename, '');
                    echo "Group cleared successfully!";
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
