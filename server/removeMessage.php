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

    // Encrypt group and group password for validation
    $groupEncrypted = openssl_encrypt($group, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');
    $grouppasswordEncrypted = openssl_encrypt($grouppassword, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');

    // Check if the group and its password are valid
    if (strpos(file_get_contents("groups.txt"), ($groupEncrypted . ":" . $grouppasswordEncrypted)) !== false) {
        
        // Encrypt admin user and admin password for validation
        $adminUserEncrypted = openssl_encrypt($adminUser, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');
        $adminPasswordEncrypted = openssl_encrypt($adminPassword, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');

        // Check if the admin user and password are valid
        if (strpos(file_get_contents("users.txt"), ($adminUserEncrypted . ":" . $adminPasswordEncrypted)) !== false) {

            // Check if the admin user is an admin
            if (strpos(file_get_contents("admins.txt"), $adminUser . "|") !== false) {
                
                // Read the group file
                $filename = "groups/" . $group . ".txt";
                if (file_exists($filename)) {
                    $fileContents = file_get_contents($filename);
                    $messages = explode("|", $fileContents);
                    $newMessages = [];
                    $messageFound = false;

                    // Encrypt the target user and text for comparison
                    $targetUserEncrypted = openssl_encrypt($targetUser, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');
                    $textEncrypted = openssl_encrypt($text, 'AES-256-CBC', "aJBKnjVGHvOHvjkvgJHBbhjGCFXdVSERTDXGbhjGYFhnjjxfdshfJLJL", 0, 'e3b0c44298fc1c14');

                    // Iterate over messages in reverse order and remove the last found one
                    for ($i = count($messages) - 1; $i >= 0; $i--) {
                        if ($messages[$i] != "") {
                            $parts = explode(":", $messages[$i]);
                            if (count($parts) == 4 && $parts[2] == $targetUserEncrypted && $parts[3] == $textEncrypted) {
                                // This is the last found message to remove, so we skip adding it to the new messages
                                $messageFound = true;
                                continue;
                            }
                            $newMessages[] = $messages[$i];
                        }
                    }

                    // Reverse the order of messages back to the original order
                    $newMessages = array_reverse($newMessages);

                    if ($messageFound) {
                        // Save the updated messages back to the file
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
