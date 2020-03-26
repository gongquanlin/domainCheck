
<?php
//下面是批量查询的
/*
$accounts=json_decode(file_get_contents("accountCookie.json"),1);
$wxuin=array_keys($accounts);
$mh = curl_multi_init();
$chs=[];

for($i=0;$i<count($wxuin);$i++)
{
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
		"Cookie:".$accounts[$wxuin[$i]]["wxcookie"],
	));
	curl_setopt($ch,CURLOPT_URL,$accounts[$wxuin[$i]]["xtURL"]);
	curl_multi_add_handle($mh,$ch);
	array_push($chs,$ch);
}

$active = null;
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);


while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}
$res=[];
for($i=0;$i<count($chs);$i++)
{
	$back = curl_multi_getcontent($chs[$i]);
	if(strpos($back,'retcode:"110')){
		$res[$wxuin[$i]]=0;
	}else{
		$res[$wxuin[$i]]=1;
	}
}


*/


//下面是单个查询的
$accounts=json_decode(file_get_contents("accountCookie.json"),1);
$wxuin=array_keys($accounts);

$res=[];
for($i=0;$i<count($wxuin);$i++)
{
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
		"Cookie:".$accounts[$wxuin[$i]]["wxcookie"],
	));
	curl_setopt($ch,CURLOPT_URL,$accounts[$wxuin[$i]]["xtURL"]);
	$back=curl_exec($ch);
	if(strpos($back,'retcode:"110')){
		$res[$wxuin[$i]]=0;
	}else{
		$res[$wxuin[$i]]=1;
	}
	
}


//var_dump($res);die();

?>
<html>
<head>
	<meta http-equiv="Refresh" content="3"> 
</head>
<body>
<table border=1>
<tr>
	<th width="150px">
	账号
	</th>
	<th>
	是否在线
	</th>
</tr>
	<?php
		for($i=0;$i<count($wxuin);$i++)
		{
			?>
			<tr>
				<td>
				<?php
					echo $wxuin[$i]
				?>
				</td>
				<td>
				<?php
					if($res[$wxuin[$i]])
					{
						echo "在线";
					}else{
						echo "离线";
					}
				?>
				</td>
			</tr>
			<?php
		}
	?>

</table>
<br>
请在监控根目录，创建名为"account.txt"的文本文件，并将下列文本框内的内容复制到account.txt文件并保存<br/>
<textarea cols=100 rows=15>
	<?php
		echo file_get_contents("accountCookie.json");
	?>
</textarea>
</body>
</html>

<?php
die();





/*
// 创建一对cURL资源
$ch1 = curl_init();
$ch2 = curl_init();

// 设置URL和相应的选项
curl_setopt($ch1, CURLOPT_URL, "http://lxr.php.net/");
curl_setopt($ch1, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_URL, "http://www.php.net/");
curl_setopt($ch2, CURLOPT_HEADER, 0);

// 创建批处理cURL句柄
$mh = curl_multi_init();

// 增加2个句柄
curl_multi_add_handle($mh,$ch1);
curl_multi_add_handle($mh,$ch2);

$active = null;
// 执行批处理句柄
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

// 读取数据
$content1 = curl_multi_getcontent($ch1);
$content2 = curl_multi_getcontent($ch2);
// 关闭全部句柄
curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);



*/




?>


<html>
<head>
<?php


require_once("wxcookies.php");
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch,CURLOPT_HTTPHEADER,array(
	"Cookie:".$wxcookie,
));
//curl_setopt($ch,CURLOPT_HEADER,1);
curl_setopt($ch,CURLOPT_URL,$xtURL);
$back=curl_exec($ch);
if(strpos($back,'retcode:"110'))
{
	include('mail.php');
	sendMail();
	?>
	</head>
	<body><span>已经掉线，请重新登录
	<?php
	print_r('Die');
}else{
	?>
	<meta http-equiv="Refresh" content="3"> 
	</head>
	<body><span>
	请不要关闭此网页，保持程序在线
	<?php
	print_r('Alive');
}
curl_close($ch);
?>
</span>
</body>
</html>