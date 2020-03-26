<html>
<head>
<script type="text/javascript" src="http://www.w3school.com.cn/jquery/jquery.js"></script>
<style>
a{
	text-decoration:none;
}
</style>
</head>
<?php
function urlget($url){
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	$back=curl_exec($ch);
	return $back;
	
}
if(count($_GET)<=0){
$cache=urlget("https://login.wx.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https%3A%2F%2Fwx.qq.com%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN");
if(!(strpos($cache,"ndow.QRLogin.code = 200"))){
	print_r($cache);
	exit("<br/>get QR code failed");
}
$QRuuid=str_replace(";",'',str_replace('"','',str_replace('window.QRLogin.code = 200; window.QRLogin.uuid = "','',$cache)));
?>
<img src="
<?php
echo "https://login.weixin.qq.com/qrcode/".$QRuuid;?>

">login QR code</img>
<br/>
<button onClick="javascript:window.location.href='?login=1&qruuid=<?php echo $QRuuid?>';console.log('done')">已经扫描并点击登录后，请点这里登录</button>
<?php }
	if(count($_GET)>0){
		if($_GET['login']==1)
		{//已经扫描并且点击了
			?>
			<!--<button onClick
			="javascript:window.location.href='?login=1&qruuid=<?php echo $_GET["qruuid"] ?>';console.log('done')">登录状态</button>
			<button onClick
			="javascript:window.location.href='loginwx.php';console.log('done')">重新登录</button>-->
			<?php $res=urlget("https://login.wx2.qq.com/cgi-bin/mmwebwx-bin/login?loginicon=true&uuid=".$_GET["qruuid"]);
				if(strpos($res,".code=200")){
					//登录成功
					$red_url=trim(str_replace('";','',str_replace('window.redirect_uri="','',str_replace('window.code=200;','',$res))));
					$red_url=str_replace("\r\n",'',$red_url);
                  	if(strpos($red_url,"wx2.qq.com"))
                    {
                      $head_wx="wx2";
                    }else{
                      $head_wx="wx";
                    }
					if (strpbrk($red_url, "\r\n")) {exit("illegal url");}
					$ch=curl_init();
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch,CURLOPT_URL,$red_url);
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
					curl_setopt($ch,CURLOPT_HEADER,1);
					//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
					$back=curl_exec($ch);
					$headers=explode("\r\n",$back);
					$tt=count($headers);
					$cookies="";
					for($t=0;$t<$tt;$t++){
						if(strpos($headers[$t],"et-Cookie")){
							$cache=strstr($headers[$t],"Set-Cookie");
							$pp=explode(":",$cache);
							$cache2=strstr($pp[1],";",true);
							$cookies=$cookies.explode(":",$cache2)[0].";";
						}
					}
					
					$postObj = simplexml_load_string($headers[$tt-1],'SimpleXMLElement',LIBXML_NOCDATA);
					$postObj2=json_decode(json_encode($postObj),1);
					//$postObjj=json_decode($postObj2,true);
					if(!isset($postObj2['wxuin']))
					{
						echo "登录失败，此账户不可以登录网页版微信<br/>";
						echo "<a href='loginwx.php'>继续登录</a>或者<a href='wxkeepalive2.php'>开始监控</a>";
						die();
					}
					$postdata='{"BaseRequest":{"Uin":"'.$postObj2['wxuin'].'","Sid":"'.$postObj2['wxsid'].'","Skey":"'.$postObj2['skey'].'","DeviceID":"e0"'.strval(rand(11111111111111,99999999999999)).'}}';
                  /*var_dump($postObj2);
                  die();*/
					$ch=curl_init();
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch,CURLOPT_HTTPHEADER,array(
						"Cookie:".$cookies,
					));
					curl_setopt($ch,CURLOPT_URL,"https://".$head_wx.".qq.com/cgi-bin/mmwebwx-bin/webwxinit?r=1772027198");
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
					curl_setopt($ch,CURLOPT_POST,1);
					curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
					$back=curl_exec($ch);
					$initData=json_decode($back,true);
					$synckey="";
                  	/*var_dump($back);
                  	var_dump($cookies);
                  var_dump($postdata);*/
                  
					for($ttt=0;$ttt<$initData['SyncKey']['Count'];$ttt++)
					{
						$synckey.=$initData['SyncKey']['List'][$ttt]['Key']."_".$initData['SyncKey']['List'][$ttt]['Val']."|";
					}
					$xtURL="https://webpush.".$head_wx.".qq.com/cgi-bin/mmwebwx-bin/synccheck?r=1527232171928_=1527231483101";
					$queryString="&skey=".urlencode($postObj2['skey'])."&sid=".urlencode($postObj2['wxsid']).'&uin='.$postObj2['wxuin'].'&deviceid=e363796500281151&synckey='.urlencode($synckey);
					$xtURL=$xtURL.$queryString;
					/*$wxcookies=fopen("wxcookies.php","w");
					fwrite($wxcookies,"<?php \r\n\$wxcookie='".$cookies."';\r\n\$xmltext='".$headers[$tt-1]."';\r\n\$xtURL='".$xtURL."' ?>");
					fclose($wxcookies);*/
                    $accountJson=array(
						"wxuin"=>$postObj2['wxuin'],
						"loginTime"=>time(),
						"wxcookie"=>$cookies,
						"xmltext"=>$headers[$tt-1],
						"xtURL"=>$xtURL,
					);
					$accounts=[];
					if(file_exists("accountCookie.json"))
					{
						$accounts=json_decode(file_get_contents("accountCookie.json"),1);
					}		
					$accounts[$postObj2['wxuin']]=$accountJson;
					file_put_contents("accountCookie.json",json_encode($accounts));
					echo "登录成功!<br/>";
					echo "<a href='loginwx.php'>继续登录</a>或者<a href='wxkeepalive2.php'>开始监控</a>";
					//echo "<script>setTimeout(function(){window.location.href='wxkeepalive.php'},2000)</script>";
				}else{
					echo $res;
				}
			?>
		<?php
		}
	}
?>
</html>