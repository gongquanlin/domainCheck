<?php
require_once("config.php");
function putLogin(){
    $port=8081;
    $accounts=(file_get_contents("accountCookie.json"));
    $f["abc"]=$accounts;
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://127.0.0.1:$port/login");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
        'Content-Type: application/json', 
        // 'Content-Length: ' . strlen($data_string)) 
    )); 
    curl_setopt($ch,CURLOPT_POSTFIELDS,($accounts));
    curl_exec($ch);
    curl_close($ch);
}
putLogin();
$res=file_get_contents("http://127.0.0.1:$port/checkalong?url=".urldecode($_GET["url"]));
echo $res;
die();
?>