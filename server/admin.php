<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Input Check</title>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="text" id="username" name="username" placeholder="username">
        <input type="text" id="password" name="password" placeholder="password">
        <input type="submit" value="Submit">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
        // Retrieve the input values
        $user = $_POST['username'];
        $password = $_POST['password'];

        $valid = false;

        $encryption_key = $_ENV['key'];
        $iv = $_ENV['iv'];

        $user = openssl_encrypt($user, 'AES-256-CBC', $encryption_key, 0, $iv);
        $password = openssl_encrypt($password, 'AES-256-CBC', $encryption_key, 0, $iv);

        if (strpos(file_get_contents("users.txt"), ($user . ":" . $password)) !== false) {
            $valid = true;   
        }
        if ($valid){
            $user = openssl_decrypt($user, 'AES-256-CBC', $encryption_key, 0, $iv);
            if (strpos(file_get_contents("admins.txt"), $user . "|") !== false) {                
                $response = file_get_contents("groups.txt");
                $groups = explode("|", $response);
                for ($i = 0; $i < count($groups); $i++) {
                    $things = explode(":", $groups[$i]);
                    if (count($things) >= 2) {
                        $groupname = openssl_decrypt($things[0], 'AES-256-CBC', $encryption_key, 0, $iv);
                        $grouppassword = openssl_decrypt($things[1], 'AES-256-CBC', $encryption_key, 0, $iv);
                        echo "Group Name: " . htmlspecialchars($groupname) . "             Group Password: " . htmlspecialchars($grouppassword);
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                            <input type="hidden" name="join_group" value="<?php echo htmlspecialchars($groupname); ?>">
                            <input type="hidden" name="join_password" value="<?php echo htmlspecialchars($grouppassword); ?>">
                            <input type="hidden" name="user" value="<?php echo htmlspecialchars($user); ?>">
                            <input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
                            <input type="submit" value="Join">
                        </form>
                        <?php
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                            <input type="hidden" name="user" value="<?php echo htmlspecialchars($user); ?>">
                            <input type="hidden" name="index" value="<?php echo htmlspecialchars($i); ?>">
                            <input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
                            <input type="submit" value="Remove">
                        </form>
                        <br>
                        <?php
                    } else {
                        
                    }
                }
            } else {
                echo "You are not an admin!";
            }
        } else {
            echo "Incorrect username or password.";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join_group']) && isset($_POST['join_password'])) {
        $group_to_join = $_POST['join_group'];
        $group_password = $_POST['join_password'];

        $encryption_key = $_ENV['key'];
        $iv = $_ENV['iv'];

        echo "You have joined the group: " . htmlspecialchars($group_to_join) . " with password: " . htmlspecialchars($group_password) . "<br>";
        
        $response = file_get_contents("groups/" . $group_to_join . ".txt");
        $messages = explode("|", $response);
        for ($i = 0; $i < count($messages); $i++) {
            $things = explode(":", $messages[$i]);
            $messageuser = openssl_decrypt($things[2], 'AES-256-CBC', $encryption_key, 0, $iv);
            $messagetext = openssl_decrypt($things[3], 'AES-256-CBC', $encryption_key, 0, $iv);
            echo "Username: " . htmlspecialchars($messageuser) . "             Message: " . htmlspecialchars($messagetext);
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                <input type="hidden" name="messageuser" value="<?php echo htmlspecialchars($messageuser); ?>">
                <input type="hidden" name="messagetext" value="<?php echo htmlspecialchars($messagetext); ?>">
                <input type="hidden" name="user" value="<?php echo htmlspecialchars($_POST['user']); ?>">
                <input type="hidden" name="group" value="<?php echo htmlspecialchars($_POST['join_group']); ?>">
                <input type="hidden" name="gp" value="<?php echo htmlspecialchars($_POST['join_password']); ?>">
                <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
                <input type="submit" value="Delete">
            </form>
            <br>
            <?php
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
            <input type="hidden" name="user" value="<?php echo htmlspecialchars($_POST['user']); ?>">
            <input type="hidden" name="cleargroup" value="<?php echo htmlspecialchars($_POST['join_group']); ?>">
            <input type="hidden" name="clearpassword" value="<?php echo htmlspecialchars($_POST['join_password']); ?>">
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
            <input type="submit" value="Clear">
        </form>
        <br>
        <?php
    }
        
        

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['messageuser']) && isset($_POST['messagetext'])) {
        $encryption_key = $_ENV['key'];
        $iv = $_ENV['iv'];
        
        $user=$_POST['user'];
        $password=$_POST['password'];
        $messageuser=$_POST['messageuser'];
        $messagetext=$_POST['messagetext'];
        $group=$_POST['group'];
        $grouppassword=$_POST['gp'];
        $password = openssl_decrypt($password, 'AES-256-CBC', $encryption_key, 0, $iv);

        
        $url = 'removeMessage.php?val=rm&group=' . urlencode($group) . '&admin_user=' . urlencode($user) . '&admin_password=' . urlencode($password) . '&target_user=' . urlencode($messageuser) . '&text=' . urlencode($messagetext) . '&gp=' . urlencode($grouppassword);

        echo "<script>window.location.href='" . $url . "';</script>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cleargroup']) && isset($_POST['clearpassword'])) {
        $encryption_key = $_ENV['key'];
        $iv = $_ENV['iv'];
        
        $user=$_POST['user'];
        $password=$_POST['password'];
        $group=$_POST['cleargroup'];
        $grouppassword=$_POST['clearpassword'];
        $password = openssl_decrypt($password, 'AES-256-CBC', $encryption_key, 0, $iv);

        
        $url = 'clearGroup.php?val=rm&group=' . urlencode($group) . '&admin_user=' . urlencode($user) . '&admin_password=' . urlencode($password) . '&target_user=' . urlencode($messageuser) . '&text=' . urlencode($messagetext) . '&gp=' . urlencode($grouppassword);

        echo "<script>window.location.href='" . $url . "';</script>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['index'])) {
        $encryption_key = $_ENV['key'];
        $iv = $_ENV['iv'];
        
        $user=$_POST['user'];
        $index=$_POST['index'];
        $password=$_POST['password'];
        $password = openssl_decrypt($password, 'AES-256-CBC', $encryption_key, 0, $iv);

        
        $url = 'removeGroup.php?val=rm&index=' . urlencode($index) . '&admin_user=' . urlencode($user) . '&admin_password=' . urlencode($password);

        echo "<script>window.location.href='" . $url . "';</script>";
    }
    ?>
</body>
</html>
