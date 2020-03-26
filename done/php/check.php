<?php
// 如果返回的数据里存在hasMonitor为true，就是现在正在检测，上一个检测任务未完成
// 如果存在domains，就是返回的已经检测完成的数据
set_time_limit(60);
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
$fuckup=false;
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
    if(isset($json["fuckup"])){$fuckup=true;}
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
    if(isset($json["fuckup"])){$fuckup=true;}
}
if(count($domainList)>=1){
    $resFFFF="";
	$okDomain="";
	$falseDomain="";
    foreach($domainList as $k=>$v)
    {
        $resFFFF.="$k:".($v==1?"正常":"被封")."\r\n";
		if($v)
		{
			$okDomain.=$k."\r\n";
		}else{
			$falseDomain.=$k."\r\n";
		}
    }
    file_put_contents("result.txt",$resFFFF);
	file_put_contents("okDomain.txt",$okDomain);
	file_put_contents("falseDomain.txt",$falseDomain);
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
    <h1>请在下方输入需要批量检测的域名然后点击提交</h1><br>
    <h3>一行一个</h3><br>
    <form action="" method="POST">
        <textarea name="domains" id="domains" cols="100" rows="10"><?php
            for($i=0;$i<count($domainsName);$i++)
            {
                echo $domainsName[$i]."\r\n";
            }
        ?></textarea>
        <br>
        <input type="submit" name="submit" id="submit"  value="提交" <?php if($hasMonitor ){echo "disabled";}?> />
    </form>
    
    <?php if($hasMonitor ){?>
        
        <h1>正在检测，请稍等</h1><br>
        
    <?php
    }
    ?>


    <table style="display:<?php if(count($domainList)<1){echo 'none';}?>" border=1>
    <tr>
            <th width="555px">
            域名
            </th>
            <th width="255px">
            状态
            </th>
    </tr>
    <?php
        /*
        for($i=0;$i<count($domainList);$i++){
            ?>
            <tr>
            <td>
            <?php echo $domainsName[$i]?>
            </td>
            <td>
            <?php 
            if($domainList[$domainsName[$i]]){
                echo "正常";
            }else{
                echo "被封";
            }
            ?>
            </td>
            </tr>
            <?php
        }
		*/
    ?>
    </table>
	<br/>
	<h3>正常的域名，保存在http://域名/okDomain.txt</h3>
	<table style="display:<?php if(count($domainList)<1){echo 'none';}?>" border=1>
    <tr>
            <th width="555px">
            域名
            </th>
            <th width="255px">
            状态
            </th>
    </tr>
    <?php
        
        for($i=0;$i<count($domainList);$i++){
			if($domainList[$domainsName[$i]]==0){
				continue;
			}
            ?>
            <tr>
            <td>
            <?php echo $domainsName[$i]?>
            </td>
            <td>
            <?php 
            if($domainList[$domainsName[$i]]){
                echo "正常";
            }else{
                echo "被封";
            }
            ?>
            </td>
            </tr>
            <?php
        }
    ?>
    </table>
	<br/>
	<h3>被封的域名，保存在http://域名/falseDomain.txt</h3>
	<table style="display:<?php if(count($domainList)<1){echo 'none';}?>" border=1>
    <tr>
            <th width="555px">
            域名
            </th>
            <th width="255px">
            状态
            </th>
    </tr>
    <?php
        
        for($i=0;$i<count($domainList);$i++){
			if($domainList[$domainsName[$i]]==1){
				continue;
			}
            ?>
            <tr>
            <td>
            <?php echo $domainsName[$i]?>
            </td>
            <td>
            <?php 
            if($domainList[$domainsName[$i]]){
                echo "正常";
            }else{
                echo "被封";
            }
            ?>
            </td>
            </tr>
            <?php
        }
    ?>
    </table>
    </div>
</body>
<script>

function postData(){
    $.ajax({
        url:"http://localhost:<?php echo $port;?>/check",
        async:false,
        dataType:"JSON",
        type:"GET",
        success:function(res,state,xhr)
        {
            console.log(res);
        },
        complete:function(res){
            console.log(res);
        }
    })
}
<?php
 if($hasMonitor){
     ?>
    //  如果正在监控，就定时刷新
    setTimeout(function(){
        window.location.href="check.php?monitor=1"
    },2000);  
<?php
 }else{
     if(count($domainList)>=1)
     {
         echo 'alert("检测结果已保存到本地result.txt,访问http://域名/result.txt即可下载");';
     }
 }
 if($fuckup)
 {
     echo "alert('账户已经掉线！请重新登录账户！！！');";
 }
?>
</script>
</html>
