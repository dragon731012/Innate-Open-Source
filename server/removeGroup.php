<?php
foreach ($_GET as $key => $value) {
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $cleanValue = str_replace('|', '', $escapedValue);
    $cleanValue = str_replace('/', '', $cleanValue);
    $cleanValue = str_replace('\\', '', $cleanValue);
    $_GET[$key] = $cleanValue;
}

if (isset($_GET["val"]) && $_GET["val"] == "rm" && isset($_GET["index"]) && isset($_GET["admin_user"]) && isset($_GET["admin_password"])) {

    $index = intval($_GET["index"]); // Convert index to integer
    $adminUser = $_GET["admin_user"];
    $adminPassword = $_GET["admin_password"];

    // Encrypt admin user and admin password for validation
    $adminUserEncrypted = openssl_encrypt($adminUser, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $adminPasswordEncrypted = openssl_encrypt($adminPassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

    // Check if the admin user and password are valid
    if (strpos(file_get_contents("users.txt"), ($adminUserEncrypted . ":" . $adminPasswordEncrypted)) !== false) {

        // Check if the admin user is an admin
        if (strpos(file_get_contents("admins.txt"), $adminUser . "|") !== false) {
                
            // Remove the group entry from groups.txt at the specified index
            $groupsFileContents = file_get_contents("groups.txt");
            $groups = explode("|", $groupsFileContents);
            
            if ($index >= 0 && $index < count($groups)) {
                unset($groups[$index]); // Remove the group entry at the specified index
                $newGroupsFileContents = implode("|", $groups);
                file_put_contents("groups.txt", $newGroupsFileContents);
                echo "Group removed successfully.";
            } else {
                echo "Invalid index.";
            }
        } else {
            echo "Admin user is not an admin.";
        }
    } else {
        echo "Invalid admin user or password.";
    }
} else {
    echo "Not a valid header.";
}
?>
