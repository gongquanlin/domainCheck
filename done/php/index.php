<?php
require_once("config.php");

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        检测后台
    </title>
    <style>
        body{
            /* display:flex;
            justify-content:left; */
        }
        .sidebar{
            position:fixed;
            top:0;
            left:0;
            color:white;
            background:rgb(37,37,37);
            text-align:center;
            width:25vh;
            height:100vh;
        }
        li{
            margin-top:25px;
            padding:0;
        
        }
        ul{
            padding:0;
            margin-top:15px;
            list-style:none;
        }
        .main{
            /* float:right; */
            position:fixed;
            left:25vh;
            top:0;
            height:100vh;
            width:calc(100% - 25vh);
        }
        button{
            padding:5px;
            width:10rem;
            height:2rem;
            border-radius:15rem;
            background-image: linear-gradient(to top right, #a18cd1 0%, #fbc2eb 100%);
            border:0;
            box-shadow: 0px 5px 25px  rgba(255,255,255,.5);
        }
        h1{
            background-image: linear-gradient(to right, #ff8177 0%, #ff867a 0%, #ff8c7f 21%, #f99185 52%, #cf556c 78%, #b12a5b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>微信监控平台</h1>
        <ul>
        <li><button onclick="clickMe(1)">登录账户</button></li>
        <li><button onclick="clickMe(2)">在线状态</button></li>
        <li><button onclick="clickMe(3)">批量检测</button></li>
        <li><button onclick="clickMe(4)">使用说明</button></li>
        </ul>
    </div>
    <iframe id="main" src="loginwx.php" class="main" frameborder="0"></iframe>
    
</body>
<script>
function clickMe(val){
    console.log(val);
    urls="";
    switch(val){
        case 1:
            urls="loginwx.php"
        break;
        case 2:
            urls="wxkeepalive2.php"
        break;
        case 3:
        urls="check.php"
        break;
        case 4:
        urls="readme.php"
        break;
        default:
            urls="loginwx.php"
    }
    document.getElementById("main").src=urls;
}
</script>
</html>