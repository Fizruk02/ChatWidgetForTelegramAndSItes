var teletonFeedback = {
    frame: false,
    parent:false,
    token: "",
    host: "https://teleton.me",
    sc:document.currentScript,
    init:(all=false)=>{
        let sc=teletonFeedback.sc;
        teletonFeedback.token = sc.dataset.teletontoken||sc.dataset.teletonToken;
        if(!all) teletonFeedback.create()
        let attr=[ "token="+teletonFeedback.token ];
        if(all) attr.push( "all=1" );
        
        attr.push("sessionId="+teletonFeedback.sessionid());
        let template=sc.getAttribute("template")||false;
        if(template) attr.push( "template="+template );
        //let borderRadius=sc.dataset.borderRadius||0;
        //if(borderRadius>0) teletonFeedback.frame.style.borderRadius=borderRadius+"px";
        teletonFeedback.frame.src=teletonFeedback.host+"/reviews?"+attr.join("&");
    },
    sessionid:()=>{
        
        //
        let s = tqw.cookie.get( "sessid"+teletonFeedback.token );
        if(!s||s=="")
            s=(document.location.host==="teleton.me"?tqw.cookie.get("PHPSESSID") : false )||teletonFeedback.generateSid(); 
        
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
        let sc=teletonFeedback.sc;
        let ifrm = document.createElement("iframe");
        ifrm.setAttribute("title", "teleton reviews");
        teletonFeedback.frame=ifrm;
        ifrm.setAttribute("id", "teleton-reviews-frame");
        let container = document.createElement("div");
        teletonFeedback.parent=container;
        container.appendChild(ifrm);
        container.classList.add("teleton-reviews-container");
        sc.parentNode.insertBefore(container, sc);
        /* ifrm.onload = function() { } */
        ifrm.style.width = "100%";
        ifrm.style.border = "0";
        
        
    var cssId = 'teletonCss';
    if (!document.getElementById(cssId))
    {
        var head  = document.getElementsByTagName('head')[0];
        var link  = document.createElement('link');
        link.id   = cssId;
        link.rel  = 'stylesheet';
        link.type = 'text/css';
        link.href = '//teleton.me/reviews/css/outerstyles.css?1';
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

        switch(dt.method){
            case "teletonResize":
                teletonFeedback.frame.style.minHeight=event.data.val+"px";
            break;
            case "borderRadius":
                let radius=event.data.val;
                if(radius>0) teletonFeedback.frame.style.borderRadius=radius+"px";
            break;
            case "putInContainer":
                if(event.data.val===1)
                    teletonFeedback.parent.classList.add("adaptive-container");
            break;
            case "viewAll":
                teletonFeedback.init(1)
            break;
            case "PHPSESSID":
                tqw.cookie.set( "sessid"+teletonFeedback.token , event.data.val);
            break;
        }
        switch(dt.event){
            case "auth_user":
                teletonFeedback.frame.contentWindow.postMessage({ method:"tg_auth_user", src:"tg", "auth_data":dt.auth_data }, '*');
            break;
        }
    }
}

document.addEventListener("DOMContentLoaded", ()=>{
    teletonFeedback.init(0);
});
if (window.addEventListener){
    window.addEventListener("message", teletonFeedback.lst,false);
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
















