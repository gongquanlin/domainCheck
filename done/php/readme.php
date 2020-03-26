<?php
// 如果返回的数据里存在hasMonitor为true，就是现在正在检测，上一个检测任务未完成
// 如果存在domains，就是返回的已经检测完成的数据
require_once("config.php");
$hasMonitor=false;
$domainList=[];

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

if(isset($_GET["monitor"]))
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://127.0.0.1:$port/result");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    $resData=curl_exec($ch);
    curl_close($ch);
    $json=json_decode(json_decode($resData,1),1);
    // 然后开始检测域名，如果当亲程序正在运行，就吧hasMonitor改成true，完成后改成false;
    $hasMonitor=$json["hasMonitor"];
    $domainList=$json["domains"];
}

if(isset($_REQUEST["domains"]))
{
    // $ch=curl_init();
    // curl_setopt($ch,CURLOPT_URL,"http://127.0.0.1:$port/");
    // $res=curl_exec($ch);
    // var_dump($res);
    // die();
    putLogin();

    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://127.0.0.1:$port/check");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,($_REQUEST["domains"]));
    $resData=curl_exec($ch);
    curl_close($ch);
    $json=json_decode(json_decode($resData,1),1);
    // 然后开始检测域名，如果当亲程序正在运行，就吧hasMonitor改成true，完成后改成false;
    $hasMonitor=$json["hasMonitor"];
    $domainList=$json["domains"];
}
$domainsName=array_keys($domainList);
?>
<html>
<head>
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
<style>
body{
    display:flex;
    justify-content:center;
    
}
h1{
    margin-bottom:15px;
}
textarea{
    width:100%;
}
#submit{
    width:100%;
    margin-top:3em;
    height:50px;
    border-radius:25rem;
    border:0;
    border-color:white;
    background-image: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
    box-shadow:0px 18px 38px rgba(0,0,0,.5);
}
td{
    text-align:center
}
</style>
</head>
<body>
    <div>
    <h1>使用说明</h1><br>
    <h3>先在登录账户登录需要监控的账号</h3><br>
    <h3>扫码之后，点击确定登陆，然后点击网页上的“已经扫描并点击登录后，点击这里登录”的按钮</h3><br>
    <h3>然后点击在线状态查看在线情况</h3><br>
    <h3>为了保持账户在线，请用宝塔或者一直挂着网址:</h3><br>
    <h3>http://你的域名/wxkeepalive2.php</h3><br>
    <h3>如果是本地挂机，域名写127.0.0.1就行</h3><br>
    <h3>然后进入config.php修改port端口，一定要和js中的server.js中的port相同。本地挂机，不用修改，默认8081</h3><br>

    <h1>js文件设置</h1><br>
    <h3>进入port修改端口，如果是本地挂，就不需要修改，默认8081</h3><br>
    <h3>如果想加快检测速度，请进入check.js修改线程数，默认50，如果服务器带宽大，可以调制128、256、甚至1000+</h3><br>
	<h3>单独检测接口</h3><br>
	<h3>http://你的域名/checkdomain.php?url=你要检测的网址，使用urlencode</h3><br>
    
    </div>
</body>

</html>
