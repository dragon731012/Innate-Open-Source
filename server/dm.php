<?php
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

    if (isset($_GET["val"]) && $_GET["val"]=="r"){
        $targetuser=$_GET["targetuser"];
        $users = [$_GET["currentuser"], $_GET["targetuser"]];
        sort($users);
        $e = fopen('dm/' . $users[0] . '|' . $users[1] . '.txt', 'a');
        $text=file_get_contents('dm/' . $users[0] . '|' . $users[1] . '.txt', 'a');
        $eunread = fopen('unread/' . $_GET["targetuser"] . '.txt', 'a');
        $textunread=file_get_contents('unread/' . $_GET["targetuser"] . '.txt', 'a');
        $messages=explode("|",$text);
        $messagesunread=explode("|",$textunread);
        $newtext="";
        $newtextunread="";


        $user = openssl_encrypt($_GET["currentuser"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $userpassword = openssl_encrypt($_GET["up"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents('users.txt'), ($user . ":" . $userpassword)) !== false) {
            for ($i=0;$i<(sizeof($messagesunread)-1);$i++){
                $usercontent=explode(":",$messagesunread[$i])[0];
                $messagecontent=explode(":",$messagesunread[$i])[1];
                if ($usercontent==$user && $messagecontent=="unread"){
                    $newtextunread=$newtextunread . $usercontent . ":" . "read" . "|";
                } else {
                    $newtextunread=$newtextunread . $usercontent . ":" . $messagecontent . "|";
                }
            }

            $filename='unread/' . $_GET["currentuser"] . '.txt';
            if (file_exists($filename)) {
                file_put_contents($filename, $newtextunread);
            }

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
        fclose($eunread);
    } else if (isset($_GET["val"]) && $_GET["val"]=="w"){
        $eunread = fopen('unread/' . $_GET["targetuser"] . '.txt', 'a');
        $textunread=file_get_contents('unread/' . $_GET["targetuser"] . '.txt', 'a');
        $users = [$_GET["currentuser"], $_GET["targetuser"]];
        sort($users);
        $file = fopen('dm/' . $users[0] . '|' . $users[1] . '.txt', 'a');
        $messagesunread=explode("|",$textunread);
        $newtextunread="";

        $user = $_GET["currentuser"];
        $text = $_GET["text"];
        $date = $_GET["date"];
        $offset = $_GET["offset"];
        $userpassword = $_GET["up"];

        $user = openssl_encrypt($user, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $targetuser = openssl_encrypt($_GET["targetuser"], 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $userpassword = openssl_encrypt($userpassword, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $text = openssl_encrypt($text, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $date = openssl_encrypt($date, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);
        $offset = openssl_encrypt($offset, 'AES-256-CBC', $_ENV['key'], 0, $_ENV['iv']);

        if (strpos(file_get_contents('unread/' . $_GET["targetuser"] . '.txt'), $user) !== false) {
            for ($i=0;$i<(sizeof($messagesunread)-1);$i++){
                $usercontent=explode(":",$messagesunread[$i])[0];
                $messagecontent=explode(":",$messagesunread[$i])[1];
                if ($usercontent==$user){
                    $newtextunread=$newtextunread . $usercontent . ":" . "unread" . "|";
                } else {
                    $newtextunread=$newtextunread . $usercontent . ":" . $messagecontent . "|";
                }
                file_put_contents('unread/' . $_GET["targetuser"] . '.txt', $newtextunread);
            }
        } else {
            fwrite($eunread,$user . ":unread|");
        }

        if (strpos(file_get_contents('users.txt'), ($user . ":" . $userpassword)) !== false) {
            fwrite($file,$date . ":" . $offset . ":" . $user . ":" . $text . "|");
        } else {
            echo "Failure";
        }
        fclose($file);
        fclose($eunread);
    } else {
        echo "Not a valid header.";
    }
} else {
    echo "Not a valid header.";
}
?>
