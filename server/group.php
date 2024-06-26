<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');

foreach ($_GET as $key => $value) {
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $cleanValue = str_replace('|', '', $escapedValue);
    $cleanValue = str_replace('/', '', $cleanValue);
    $cleanValue = str_replace('\\', '', $cleanValue);
    $_GET[$key] = $cleanValue;
}

if (isset($_GET["val"]) && isset($_GET["group"]) && isset($_GET["password"])){
    function containsDisallowedCharacters($input) {
        $pattern = "/[^a-zA-Z0-9_\-\'\"\,\.#@&!?+=\(\)\%\^\[\]\{\}\;\:]/";
        return preg_match($pattern, $input) === 1;
    }
    if (containsDisallowedCharacters($_GET["group"])){
        echo "Failure";
        exit;
    }
    if (isset($_GET["val"]) && $_GET["val"]=="w"){
        $file = fopen('groups.txt', 'a');
        $group = $_GET["group"];
        $password = $_GET["password"];
        if ($password==""){
            $password="none";
        }
        $valid = false;

        $group = openssl_encrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $password = openssl_encrypt($password, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents("groups.txt"), $group) !== false) {
            $valid = true;   
        }

        if ($valid==false && strtolower($group)!="users"  && strtolower($group)!="groups"){
            fwrite($file,$group . ":" . $password . "|");
        } else {
            echo "Failure";
        }
        fclose($file);
    } else if (isset($_GET["val"]) && $_GET["val"]=="r"){
        $text=file_get_contents('groups.txt');
        $groups=explode("|",$text);
        $newtext="";
        for ($i=0;$i<(sizeof($groups)-1);$i++){
            $group=explode(":",$groups[$i])[0];
            $newtext=$newtext . openssl_decrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . "|";
        }
        echo $newtext;
    } else if (isset($_GET["val"]) && $_GET["val"]=="v"){
        $group = $_GET["group"];
        $password = $_GET["password"];
        if ($password==""){
            $password="none";
        }
        $valid = false;

        $group = openssl_encrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $password = openssl_encrypt($password, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents("groups.txt"), ($group . ":" . $password)) !== false) {
            $valid = true;     
        }

        if ($valid==true){
            echo "Valid";
        } else {
            echo $group . ":" . $password;
        }
    } else {
        echo "Not a valid header.";
    }
} else {
    echo "Not a valid header.";
}
?>
