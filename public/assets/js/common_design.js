/*--------------------------------------------------------------------------*
 *  Scroll (pagetop link)
 *--------------------------------------------------------------------------*/
function scroller(i){
	scroller_up(i,1000);
}

function scroller_up(i,y){
	y = y + (i - y)*.1;
	window.scroll(0,y);
	if (((i - y) <= .6)&&((i - y) >= -.6))
	{
		y = i;
	}else{
		setTimeout("scroller_up("+i+","+y+")",1);
	}
}
function scroller_e(i){
	y = 1;
	kyoukai = i*.6;
	while(y <= kyoukai)
	{
		window.scroll(0,y);
		y = y + (y*.08);
	}
	while(y != i)
	{
		window.scroll(0,y);
		y = y + (i-y)*.08;
		if (((i - y) <= .6)&&((i - y) >= -.6))
		{
			y = i;
		}
	}
}

new function(){
	var footerId = "sh_fc2footer_fix";
	//メイン
	function footerFixed(){
		//ドキュメントの高さ
		var dh = document.getElementsByTagName("body")[0].clientHeight;
		if (document.getElementById(footerId)) {
			//フッターのtopからの位置
			document.getElementById(footerId).style.top = "0px";
			var ft = document.getElementById(footerId).offsetTop;
			//フッターの高さ
			var fh = document.getElementById(footerId).offsetHeight;
			//ウィンドウの高さ
			if (window.innerHeight){
				var wh = window.innerHeight;
			}else if(document.documentElement && document.documentElement.clientHeight != 0){
				var wh = document.documentElement.clientHeight;
			}
			if(ft+fh<wh){
				document.getElementById(footerId).style.position = "relative";
				document.getElementById(footerId).style.top = (wh-fh-ft-1)+"px";
			}
		}
	}

	//文字サイズ
	function checkFontSize(func){
		//判定要素の追加
		var e = document.createElement("div");
		var s = document.createTextNode("S");
		e.appendChild(s);
		e.style.visibility="hidden"
		e.style.position="absolute"
		e.style.top="0"
		document.body.appendChild(e);
		var defHeight = e.offsetHeight;
		
		//判定関数
		function checkBoxSize(){
			if(defHeight != e.offsetHeight){
				func();
				defHeight= e.offsetHeight;
			}
		}
		setInterval(checkBoxSize,1000)
	}

	//イベントリスナー
	function addEvent(elm,listener,fn){
		try{
			elm.addEventListener(listener,fn,false);
		}catch(e){
			elm.attachEvent("on"+listener,fn);
		}
	}

	addEvent(window,"load",footerFixed);
	addEvent(window,"resize",footerFixed);
	
	
	var CNAME='fclo';var COOKIE_DOMAIN='fc2.com';var ua=navigator.userAgent;var d=new Date();var lang=(navigator.language)?navigator.language:navigator.userLanguage;var now=new Date();var ja1=new Date(now.getFullYear(),0,1,0,0,0,0);var tmp=ja1.toGMTString();var ja2=new Date(tmp.substring(0,tmp.lastIndexOf(" ")-1));var std_time_offset=(ja1-ja2)/(1000*60*60);var ju1=new Date(now.getFullYear(),6,1,0,0,0,0);tmp=ju1.toGMTString();var ju2=new Date(tmp.substring(0,tmp.lastIndexOf(" ")-1));var daylight_time_offset=(ju1-ju2)/(1000*60*60);var dst='';if(std_time_offset!=daylight_time_offset){dst=' DST';}
var nowtime=new Date().getTime();var clear_time=new Date(nowtime+(60*60*24*1000*365));var expires=clear_time.toGMTString();function getCookieDomain(){var host=location.host;var m=host.match(/([a-z0-9][a-z0-9\-]{1,63}\.[a-z]{2,6})$/);if(m&&m.length>0){return m[0];}
return COOKIE_DOMAIN;}
var domain=getCookieDomain();var cval=CNAME+'='+escape(now.getTime()+','+lang+','+std_time_offset+dst)+';domain=.'+domain+';path=/;expires='+expires;document.cookie=cval;
}
