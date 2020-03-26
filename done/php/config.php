<?php
$port=8081;//nodejs程序的端口号，默认8081 
function closeErrorReport(){
//************************屏蔽错误信息和警告

	ini_set("display_errors", 0);

	error_reporting(E_ALL ^ E_NOTICE);

	error_reporting(E_ALL ^ E_WARNING);
}



//************************基础设置
//数据库IP
$db_ip="localhost";
//数据库账号
$db_user_name="root";
//数据库密码
$db_user_password="gong15098948763";
//数据库名
$db_name="wx_checkdomain";
//测试模式
$debugMode=1;
//************************增加设置
//超级管理员账号
$superAdmin="15000000000";
//默认测试天数
$testDay=1;
//默认初始链接数
$defaultNum=1;
//密码盐
$pwdSalt='DSF798FD*#DSF34a4F$FDsf78';

//************************数据库登录
if(!$debugMode)
{
	closeErrorReport();
}
/*
$con=mysqli_connect($db_ip,$db_user_name,$db_user_password,$db_name);

if(!$con)
{
	$con->close();
	if($debugMode)
	{
		var_dump("Connection failed:".$con->connect_error);
	}
	die();
}

//选择数据库
$con->select_db($db_name);

//设置字符集
$con->set_charset("utf8");
*/
if(!isset($_SESSION))
{
	session_start();
}
?>