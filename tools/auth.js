var teletonAuth = {
    frame: false,
    parent:false,
    token: "",
    host: "https://teleton.me",
    sc:document.currentScript,
    redirect:{
        authUrl:false,
    },
    onauth:false,
    style:{
      bgColor:"",  
      textColor:"",  
    },
    init:()=>{
        let sc=teletonAuth.sc;

        teletonAuth.token = sc.dataset.teletonToken;
        
        teletonAuth.redirect.authUrl=sc.dataset.authUrl||!1;
        
        teletonAuth.onauth = sc.dataset.onauth||!1;
        
        let bgColor = sc.dataset.bgColor,
            textColor = sc.dataset.textColor;
        teletonAuth.create()
        let attr=[ "token="+teletonAuth.token ];
        
        //attr.push("sessionId="+teletonAuth.sessionid());
        //let borderRadius=sc.dataset.borderRadius||0;
        //if(borderRadius>0) teletonAuth.frame.style.borderRadius=borderRadius+"px";
        if( bgColor ) attr.push( "bgColor="+bgColor );
        if( textColor ) attr.push( "bgColor="+textColor );
        let ssid = localStorage.getItem('stelt_ssid')||!1;
        if(ssid) attr.push( "stelt_ssid="+ssid );
        teletonAuth.frame.src=teletonAuth.host+"/tools/auth?"+attr.join("&");
    },
    sessionid:()=>{
        
        //
        let s = tqw.cookie.get( "sessid"+teletonAuth.token );
        if(!s||s=="")
            s=(document.location.host==="teleton.me"?tqw.cookie.get("PHPSESSID") : false )||teletonAuth.generateSid(); 
        
        return s;
    },
    generateSid:()=>{
        let charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        res = '';
        for (var i = 0, n = charset.length; i < 20; ++i)
            res += charset.charAt(Math.floor(Math.random() * n));
        return res;
    },
    create:()=>{
        let sc=teletonAuth.sc;
        let ifrm = document.createElement("iframe");
        ifrm.setAttribute("title", "teleton auth");
        teletonAuth.frame=ifrm;
        ifrm.setAttribute("id", "teleton-auth-frame");
        let container = document.createElement("div");
        teletonAuth.parent=container;
        container.appendChild(ifrm);
        container.classList.add("teleton-auth-container");
        sc.parentNode.insertBefore(container, sc);
        /* ifrm.onload = function() { } */
        ifrm.style.border = "0";
        ifrm.style.height = "0";
        ifrm.style.width = "0";
        
        
    var cssId = 'teletonCss';
    if (!document.getElementById(cssId))
    {
        var head  = document.getElementsByTagName('head')[0];
        var link  = document.createElement('link');
        link.id   = cssId;
        link.rel  = 'stylesheet';
        link.type = 'text/css';
        link.href = '//teleton.me/tools/auth/css/outerstyles.css';
        link.media = 'all';
        head.appendChild(link);
    }
        
        
    },
    lst:event=> {
        //if(event.origin!==window.location.origin) return;
        let dt=event.data;
        if(typeof dt=="string"){
            try {
              dt=JSON.parse(dt);
            } catch (e) { return; }
        } 

        switch(dt.event){
            case "teletonAuthResize":
                teletonAuth.frame.style.minHeight=dt.result.h+"px";
                teletonAuth.frame.style.minWidth=dt.result.w+"px";
            break;
            case "teletonAuth":
                dt.result["teletonAuth"]=1;
                let user = dt.result;
                if(url=teletonAuth.redirect.authUrl) document.location.href=url+(url.indexOf("?")>-1?"&":"?")+teletonAuth.gpar( user );
                if(func = teletonAuth.onauth){
                    let dt=func.split("(");
                    let par = dt[1]||user;
                    eval(dt[0])(par==="user)"?user:par);
                } 
            break;
            case "teletonAuthSsid":
                localStorage.setItem('stelt_ssid', dt.result);
            break;
        }
    },
    gpar:a=>{
        var l=[];
        for(i in a) l.push(i+"="+encodeURIComponent(a[i]));
        return l.join("&");
    },
}

document.addEventListener("DOMContentLoaded", ()=>{
    teletonAuth.init();
});
if (window.addEventListener){
    window.addEventListener("message", teletonAuth.lst,false);
}

var tqw={
    qs:(e)=> {return tqw.qsa(e)[0]},
    qsa:(e)=> {try {return document.querySelectorAll(e)} catch (v){return []} },
    gpars:(s)=> {let e; return (e = (s?s:document.URL).split("?")[1]) ? JSON.parse('{"' + e.replace(/&/g, '","').replace(/=/g, '":"') + '"}') : []},
    cookie:{
        ch:()=>{if(navigator.cookieEnabled === false) return console.warn('Cookies are disabled!');return 1;},
        set:(n, v, p=30)=>{ if(!tqw.cookie.ch()) return;
            let d = new Date();
            d.setTime(d.getTime() + (p * 24 * 60 * 60 * 1000));
            document.cookie = n + "=" + v + "; expires=" + d.toGMTString() + "; path=/"; 
        },
        get:(n)=>{ if(!tqw.cookie.ch()) return;
            let m = n + "=",a = document.cookie.split(';'),i,c;
            for (i = 0; i < a.length; i++)
                if ((c = a[i].trim()).indexOf(m) == 0) {
                    let f=c.substring(m.length, c.length);
                    return f
                };
            return "";
        },
        delete:(n)=>{ if(!tqw.cookie.ch()) return;
            document.cookie = n+"=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
        }
    },
}
















