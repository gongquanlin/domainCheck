/*
通过设置nullDomain还是存在误报，但是频率已经大大降低
但是nullDomain还是没有插进去，肯定还有错误
nullDomain循环检测那边，直接就break了 不知道为啥,141行，明天检测下
*/







//express_demo.js 文件

$port=8081;//批量检测，内网监控端口
size=2000;//平均分成2000个1组，如果为0不分组 
nullDomainTimer=10;//无效检测重试次数，控制nullDomain的，部分情况下请求提多会返回空，为避免锁死，以此控制重试次数

var accounts={};
var check=require("./check.js");
var express = require('express');
var app = express();
var bodyParser = require('body-parser');

var domainsCache={};
var nullDomain=[];//部分请求太多的时候，返回的FullURL为空。为空的为无效，需要重新测试

//解决传递文件大小限制
app.use(bodyParser.urlencoded({
  extended:true,
  limit:'50mb'
}));
app.use(bodyParser.json());
app.use(express.json({limit: '50mb'}));


var hasMonitor=false;

app.all('*', function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    // res.header("Access-Control-Allow-Headers", "X-Requested-With");
    res.header('Access-Control-Allow-Headers', 'Content-Type, Content-Length, Authorization, Accept, X-Requested-With , yourHeaderFeild');
    res.header("Access-Control-Allow-Methods","PUT,POST,GET,DELETE,OPTIONS");
    res.header("X-Powered-By",' 3.2.1')
    res.header("Content-Type", "application/json;charset=utf-8");
    next();
});

fuckup=false;

function sliceArr(array, size) {
	return new Promise((resolve,reject)=>{
		var result = [];
                for (var x = 0; x < Math.ceil(array.length / size); x++) {
                    var start = x * size;
                    var end = start + size;
                    result.push(array.slice(start, end));
                }
                resolve(result);
	})
}

async function startCHeck($domains,accounts){
  domainsCache={};
  nullDomain=[];
    fff=0;
    var monitorF=function(i,r,n=null){
      fff=fff+1;
      domainsCache[i]=r;
	  if(n!=null && nullDomain.indexOf(n)==-1)
	  {//如果n不为null且不存在nullDomain中，则添加（避免重复添加）
		nullDomain.push(n);
	  }
    }
    var monitors=setInterval(function(){
      console.log("当前已检测"+fff+"个，共"+$domains.length+"个");
      //console.log("domain cache is ");
      //console.log(domainsCache);
    },1000);
    fuckup=false;
	domainGroup=[];
	if(size!=0)
	{
		domainGroup=await sliceArr($domains,size);
		console.log("共%s组",domainGroup.length);
		for(i=0;i<domainGroup.length;i++)
		{
			var resf=await check.checkDomain(domainGroup[i],accounts,monitorF).then().catch((e)=>{
				console.log(e);
				console.log("账户已经掉线！！！");
				fuckup=true;
			});
			console.log("第%s组域名完成",i)
		}
		
	}else{
		var resf=await check.checkDomain($domains,accounts,monitorF).then().catch((e)=>{
			console.log(e);
			console.log("账户已经掉线！！！");
			fuckup=true;
		});
	}
   nullDomainTimes=0;
	console.log("nullDomain is ");
	console.log(nullDomain);
	nullDomain2=[];
	while(nullDomain.length>0)
	{
		var monitorF=function(i,r,n=null){
		  fff=fff+1;
		  domainsCache[i]=r;
		  if(n!=null && nullDomain2.indexOf(n)==-1)
		  {//如果n不为null且不存在nullDomain2中，则添加（避免重复添加）
			nullDomain2.push(n);
		  }
		}
		
		if(nullDomain.length>0 && nullDomain2.length>0)
		{
			console.log("fuck2");
			console.log(nullDomain);
			console.log(nullDomain2);
			console.log(nullDomain.length>0);
			console.log(nullDomain2.length>0);
			nullCacheDomain=nullDomain2.slice(0);
			nullDomain=nullDomain2.slice(0);
			console.log(nullDomain.length>0 && nullDomain2.length>0);
		}else{
			if(nullDomainTimes==0)
			{
				//如果是第一次循环
				nullCacheDomain=nullDomain.slice(0);
			}else{
				nullCacheDomain=nullDomain2.slice(0);
				nullDomain=nullDomain2.slice(0);
				console.log("fuck1");
				console.log(nullDomain);
			}
		}
		nullDomain2=[];
		console.log("现在nullDomain是");
		console.log(nullDomain);
		var resf=await check.checkDomain(nullCacheDomain,accounts,monitorF).then().catch((e)=>{
			console.log(e);
			console.log("账户已经掉线！！！");
			fuckup=true;
		});
		console.log("现在nullDomain2是");
		console.log(nullDomain2);
		nullDomainTimes+=1;
		console.log("计次是");
		console.log(nullDomainTimes);
		if(nullDomainTimer<=nullDomainTimes || nullDomain2==[])
		{//难道是这里或者nullDomainTimer?
			console.log("即将跳出");
			console.log(nullDomainTimes);
			console.log(nullDomain2);
			break;
		}
	}
	console.log("start delete item");
	for(var i in nullDomain)
	{
		console.log(i)
		delete domainsCache[nullDomain[i]]
	}
	
   clearInterval(monitors);
   
   delete domainsCache["cache"];
   
   console.log("监控完成");

   hasMonitor=false;
}



app.get('/', function (req, res) {
   res.send('Hello World');
})
app.get("/result",function(req,res){
  resJson={"hasMonitor":hasMonitor,"domains":domainsCache}
  res.jsonp(JSON.stringify(resJson));
})


app.get('/checkalong',async function (req, res) {
    console.log(req.query.url);//为获得域名
	var f=0;
	 var resf=await check.checkDomain([req.query.url],accounts).then().catch((e)=>{
		 f=1;
      });
	  if(f)
	  {
		  resfa={"code":500,"msg":"账户已经掉线","domain":{}}
	  }else{
		  resfa={"code":200,"msg":"查询成功","domain":resf}
	  }
	  console.log(resf);
    res.jsonp(JSON.stringify(resfa));
 })


app.post('/check',async function (req, res) {
  // { 'dasfdfas\r\nfdasadsfdfsadsfa\r\nafdsafsdafdsadfsafdsfdas': '' }
    console.log(req.body);
    // resJson={"hasMonitor":hasMonitor,"domains":{"baidu.com":1,"cnzz.com":0,"baiduasdfasdf.com":1}}
    var domainsSource="";
    for(var key in req.body)
    {
      domainsSource=key;
      break;
    }
    $domains=domainsSource.split('\r\n');
    if(hasMonitor)
    {
      resJson={"hasMonitor":hasMonitor,"domains":domainsCache};
	  if(fuckup){
		  resJson["fuckup"]=1;
	  }
      res.jsonp(JSON.stringify(resJson));
      return;
    }
    
    
    
    hasMonitor=true;
    startCHeck($domains,accounts);
    // var resf=await check.checkDomain($domains,accounts,monitorF).then().catch((e)=>{
    //   console.log("account doen!!!");
    //  });

    
    resJson={"hasMonitor":hasMonitor,"domains":domainsCache}
    res.jsonp(JSON.stringify(resJson));
 })
 app.post('/login',async function (req, res) {
    console.log(req.body);
    // var domainsSource="";
    // console.log($domains);
    accounts=req.body;
    resJson={"hasMonitor":hasMonitor,"domains":{"baidu.com":1,"cnzz.com":0,"baiduasdfasdf.com":1}}
    res.jsonp(JSON.stringify(resJson));
 })
 
 app.get('*', function(req, res){
  console.log("404 coming!!!!!!!!!!!!!!!!!!");
  /*res.render('404.html', {
      title: 'No Found'
  })*/
})

var server = app.listen($port, function () {
 
  var host = server.address().address
  var port = server.address().port
 
  console.log("应用启动，接口地址为 http://%s:%s", host, port)
 
})