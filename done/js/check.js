/*
采用严格被封模式，
*/


threads=100;//线程数
spliceTime=1;//两线程之间间隔秒数
request=require("request");
async=require("async");

logoutTimer=0;

accountCount={};//如果同一个微信号同时检测3000个以上会误报，因此每个微信账户只允许检测3000个同时
//在Array原型上创建一个random方法
Array.prototype.random = function () {
    var idx = Math.floor((Math.random() * (this.length-1)));
    //var n = this.slice(idx, idx+1)[0];//或者用下面splice()
	var c=this.slice(0);
    var n = c.splice(idx,1)[0];
    return n;
}

 function getDomains(dirs="domain.txt"){
    
    return new Promise((resolve,reject)=>{
        const fs = require("fs");

        fs.readFile(dirs, "utf-8", function(error, data) {

        if (error) {reject(error);return ;}
        //   //2//console.log("读取文件成功,内容是" + data);
            // //2//console.log(data);
            let domains=data.split("\r\n");
            resolve(domains);
            // //2//console.log("done");
        });
    });
}
function checkDomain(domains,accounts,monitor=function(){}){
    return new Promise((resolve,reject)=>{
            indexTimes=0;
            var accountUin=[];
            for(var i in accounts)
            {
                accountUin.push(i);
            }
            async.mapLimit(domains,threads,function(i,callback){
                // monitorCallback(i);
                // 上面这个是个监控函数，只有在阻塞情况下监控可以用。参考cnzz里的test.js
                indexTimes++;
                if(!i)
                {
                    callback(null,null);
                    return;
                }
                setTimeout(function(){
					randomAccount=accountUin.random();//随机取一个现有账户
					var f=request(checkDomainModel(i,accounts[randomAccount]["wxcookie"],accounts[randomAccount]["xtURL"]),
                    initRequestAsync(i,randomAccount,callback,monitor)
                );
				},spliceTime*Math.random()*1000);//每一个间隔1秒开始
            },function(e,r){
				logoutTimer=0;
                if(e)
                {
                    /*delete accounts["r"];*/
                    reject(e);
                }else{
                    var ress={};
                    for(var i in domains)
                    {
                        ress[domains[i]]=0;
                    }
                    for(var i in r)
                    {
                        if(i)
                        {
                            if(r[i]==null)
                            {
                                continue;
                            }
                            ress[r[i]]=1;
                        }
                    }
                    resolve(ress);
                }
            });
        })
}

function initRequestAsync(index,account,callback2,monitor=function(){}){
	
    // index表示当前域名,account表示当前账户Uin,monitor就是监控，用来实时将数据写入缓存
	var callback=function (error, response, body) {

				if (!error && (response.statusCode == 200 || response.statusCode == 301)) {

                    
					resss=body;
					//console.log("body is ");
					//console.log(resss);
					/*
					console.log(resss);
					callback2(1,index);//中断测试
					*/
                    if(resss)
                    {
						//newredirectconfirmcgi
                        if(response.statusCode == 200 && !resss.includes('newreadtemplate'))
                        {

                            // $res=strpos($ret_js['FullURL'],"https://weixin110.qq.com/cgi-bin/mmspamsupport-bin/newredirectconfirmcgi?main_type=2");
                            // 如果找到了就是ban了，没找到就是正常
                            monitor(index,1);
                            callback2(null,index);
                        }else if(response.statusCode == 301 && !resss.includes('newredirectconfirmcgi')){
							if(resss.includes('"FullURL": "",'))
							{
								//怀疑频率太高，延迟5秒，这里是因为返回的fullurl为空，为空不能判断是否被ban，需要重新检测
								setTimeout(function(){
									//console.log("FullURL返回空");
									//console.log(resss);
									monitor("cache",0,index);
									callback2(null,null);
									
								},5000)
							}else{
								monitor(index,1);
								callback2(null,index);

							}
                            
						}else{
                            monitor(index,0);
							//console.log("也没有200也没有301或者包含被封字段");
							//console.log(resss);
							//console.log(response.statusCode);
                            callback2(null,null);
                        }
                    }else{
                        //callback2("logout",account);
						//console.log("怀疑掉线，暂停60秒")
						//console.log((response.statusCode))
						monitor("cache",0,index);
						//这里应该是掉线，大量掉线再报错。有的服务器防止扫描肉鸡，需要等待2分钟才可以继续
						if(logoutTimer<=threads*5)
						{
							logoutTimer+=1;
							setTimeout(function(){
								callback2(null,null);
							},60000);//防止某些服务器劫持扫肉鸡的功能误判，有可能每3000个就直接返回空，所以设置1分钟睡眠

						}else{

							callback2("logout",account);
						}
                    }
				}else{
					console.log("get error");
					console.log(error);
					if(error.code='ETIMEDOUT')
					{
						callback2(null,null);//找不到dns的ip就是可以跳转，统一算挂了
					}else{
						callback2(null,null);//成功返回index，失败返回null
					}
                    
                    monitor("cache",0,index);
				}
				//无论是否成功，已经完成的放在这里
			}
	return callback;
}

function checkDomainModel(domain,cookie,xtURL)
{
    //2//console.log("cookie is ");
    //2//console.log(cookie);
    if(xtURL.includes("wx.qq.com"))
    {
        url="https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl="+encodeURIComponent(domain);
    }else{
        url="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl="+encodeURIComponent(domain);
    }
    headers = {
         'Cookie': (cookie),
      };
  let opts = {
        url: url,
        method: 'GET',
        headers: headers,
        timeout:5*1000,
		followRedirect:false,
		followRedirects:false
  };
    return opts;
}

function login(dirs="account.txt"){
    return new Promise((resolve,reject)=>{
        const fs = require("fs");

        fs.readFile(dirs, "utf-8", function(error, data) {

        if (error) {reject(error);return ;}
        //   //2//console.log("读取文件成功,内容是" + data);
            // //2//console.log(data);
            var accounts=JSON.parse(data)
            resolve(accounts);
            // //2//console.log("done");
        });
    });
}
exports.getDomains=getDomains;
exports.login=login;
exports.checkDomain=checkDomain;