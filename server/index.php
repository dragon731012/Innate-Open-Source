<?php
foreach ($_GET as $key => $value) {
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $cleanValue = str_replace('|', '', $escapedValue);
    $_GET[$key] = $cleanValue;
}

if (isset($_GET["val"]) && isset($_GET["group"]) && isset($_GET["user"]) && isset($_GET["text"]) && isset($_GET["up"]) && isset($_GET["gp"])){

if (isset($_GET["val"]) && $_GET["val"]=="w" && strpos(strtolower($_GET["group"]),"users")==false){
    $file = fopen("groups/" . $_GET["group"] . ".txt", 'a');

    $user = $_GET["user"];
    $text = $_GET["text"];
	$date = $_GET["date"];
	$offset = $_GET["offset"];
    $userpassword = $_GET["up"];
    $grouppassword = $_GET["gp"];

    if ($grouppassword=="" || $grouppassword==null){
        $grouppassword="none";
    }

    $user = openssl_encrypt($user, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $userpassword = openssl_encrypt($userpassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $text = openssl_encrypt($text, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $grouppassword = openssl_encrypt($grouppassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
	$date = openssl_encrypt($date, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
	$offset = openssl_encrypt($offset, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);


    if (strpos(file_get_contents('users.txt'), ($user . ":" . $userpassword)) !== false) {
        fwrite($file,$date . ":" . $offset . ":" . $user . ":" . $text . "|");
    } else {
        echo "Failure";
    }
    fclose($file);
} else if (isset($_GET["val"]) && $_GET["val"]=="r"){
    $e = fopen("groups/" . $_GET["group"] . '.txt', 'a');
    $text=file_get_contents("groups/" . $_GET["group"] . '.txt');
    $messages=explode("|",$text);
    $newtext="";

    $group = $_GET["group"];
    $grouppassword = $_GET["gp"];
    if ($grouppassword=="" || $grouppassword==null){
        $grouppassword="none";
    }

    $grouppassword = openssl_encrypt($grouppassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    $group = openssl_encrypt($group, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
    
    if (strpos(file_get_contents("groups.txt"), ($group . ":" . $grouppassword)) !== false) {
        for ($i=0;$i<(sizeof($messages)-1);$i++){
			$date=explode(":",$messages[$i])[0];
			$offset=explode(":",$messages[$i])[1];
            $user=explode(":",$messages[$i])[2];
            $message=explode(":",$messages[$i])[3];
            $newtext=$newtext . openssl_decrypt($date, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . ":" . openssl_decrypt($offset, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . ":" . openssl_decrypt($user, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . ":" . openssl_decrypt($message, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']) . "|";
        }
    }
    echo $newtext;
    fclose($e);
} else {
    echo "Not a valid header.";
}

} else {
    echo "Not a valid header.";
}
?>
