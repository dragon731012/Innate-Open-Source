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

if (isset($_GET["val"]) && isset($_GET["currentuser"]) && isset($_GET["targetuser"])  && isset($_GET["up"])){
    function containsDisallowedCharacters($input) {
        $pattern = "/[^a-zA-Z0-9_\-\'\"\,\.#@&!?+=\(\)\%\^\[\]\{\}\;\:]/";
        return preg_match($pattern, $input) === 1;
    }
    if (containsDisallowedCharacters($_GET["targetuser"])){
        echo "Failure";
        exit;
    }
    if (isset($_GET["val"]) && $_GET["val"]=="w"){
        $file = fopen('opendm/' . $_GET["currentuser"] . '.txt', 'a');
        $otherfile = fopen('opendm/' . $_GET["targetuser"] . '.txt', 'a');
        
        $text=file_get_contents('opendm/' . $_GET["currentuser"] . '.txt', 'a');

        $dms=explode("|",$text);
        $newtext="";
        $failed=false;
        for ($i=0;$i<(sizeof($dms)-1);$i++){
            $temp=openssl_decrypt($dms[$i], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
            echo $temp . "<br>";
            if ($temp==$_GET["targetuser"]){
                $failed=true;
            }
        }

        $user = openssl_encrypt($_GET["currentuser"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $userpassword = openssl_encrypt($_GET["up"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $targetuser = openssl_encrypt($_GET["targetuser"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents('users.txt'), ($user . ":" . $userpassword)) !== false && !$failed) {
            fwrite($file,$targetuser . "|");
            fwrite($otherfile,$user . "|");
        } else {
            echo "Failure";
        }
        fclose($file);
        fclose($otherfile);
    } else if (isset($_GET["val"]) && $_GET["val"]=="r"){
        $text=file_get_contents('opendm/' . $_GET["currentuser"] . '.txt', 'a');

        $user = openssl_encrypt($_GET["currentuser"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $userpassword = openssl_encrypt($_GET["up"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents('users.txt'), ($user . ":" . $userpassword)) !== false) {
            $dms=explode("|",$text);
            $newtext="";
            for ($i=0;$i<(sizeof($dms)-1);$i++){
                $newtext=$newtext . openssl_decrypt($dms[$i], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . "|";
            }
            echo $newtext;
        }
    } else {
        echo "Not a valid header.";
    }
} else {
    echo "Not a valid header.";
}
?>
