<?php 
foreach ($_GET as $key => $value) {
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $cleanValue = str_replace('|', '', $escapedValue);
    $cleanValue = str_replace('/', '', $cleanValue);
    $cleanValue = str_replace('\\', '', $cleanValue);
    $_GET[$key] = $cleanValue;
}

if (isset($_GET["val"]) && isset($_GET["user"]) && isset($_GET["password"])){
    function containsDisallowedCharacters($input) {
        $pattern = "/[^a-zA-Z0-9_\-\'\"\,\.#@&!?+=\(\)\%\^\[\]\{\}\;\:]/";
        return preg_match($pattern, $input) === 1;
    }
    if (containsDisallowedCharacters($_GET["user"])){
        echo "Failure";
        exit;
    }
if ($_GET["val"]=="w"){
    $file = fopen('users.txt', 'a');
    $user = $_GET["user"];
    if (!str_contains(strtolower(trim($user)),"potato.")){
        $loweruser = strtolower($user);
        $password = $_GET["password"];
        $valid = false;
        
        $user = openssl_encrypt($user, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $loweruser = openssl_encrypt($loweruser, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $password = openssl_encrypt($password, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents("users.txt"), $user) !== false) {
            $valid = true;     
        }
        
        if ($valid==false){
            fwrite($file,$user . ":" . $password . "|");
        } else {
            echo "Failure";
        }
        fclose($file);
    }
} else if ($_GET["val"]=="r"){
    $valid = false;
    $user = $_GET["user"];
    $password = $_GET["password"];

    $user = openssl_encrypt($user, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $password = openssl_encrypt($password, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

    if (strpos(file_get_contents("users.txt"), ($user . ":" . $password)) !== false) {
        $valid = true;   
    }
    if ($valid){
        echo "Valid";
    } else {
        echo "Failure";
    }
} else {
    echo "Not a valid header.";
}
}
?>
