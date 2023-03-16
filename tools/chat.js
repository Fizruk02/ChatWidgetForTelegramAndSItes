var teletonChat = {
    frame: false,
    parent:false,
    token: "",
    host: "https://teleton.me",
    sc:document.currentScript,
    init:(all=false)=>{
        let sc=teletonChat.sc;
        
        teletonChat.create();
        // teletonFeedback.token = sc.dataset.teletontoken||sc.dataset.teletonToken;
        // if(!all) teletonFeedback.create()
        // let attr=[ "token="+teletonFeedback.token ];
        // if(all) attr.push( "all=1" );
        let attr=[];
        attr.push("sess="+twcqw.cookie.get( "CWSESS"));
        // let template=sc.getAttribute("template")||false;
        // if(template) attr.push( "template="+template );


        let btn=document.createElement("div");
        btn.classList.add("feedback");
        btn.innerHTML = "Написать";
        btn.onclick=()=> {
            //wgc.msgNew.classList.remove("show");
            //btn.style.display = "none";
            teletonChat.parent.classList.add("show");
        }

        document.body.append(btn);
        
        teletonChat.frame.src=teletonChat.host+"/chatWidget?"+attr.join("&");
    },
    
    create:()=>{
        let sc=teletonChat.sc;
        let ifrm = document.createElement("iframe");
        ifrm.setAttribute("title", "teleton chat");
        teletonChat.frame=ifrm;
        ifrm.setAttribute("id", "teleton-chat-widget-frame");
        let container = document.createElement("div");
        let header = document.createElement("div"),
            title = document.createElement("div"),
            close = document.createElement("div");
        header.classList.add("teleton-chat-head");
        title.classList.add("tch-title");
        close.classList.add("tch-close");
        title.innerHTML = "Чат виджет";
        close.innerHTML = "&#10006;";
        header.append(title);
        header.append(close);
        container.append(header);
        close.onclick=()=> {
            teletonChat.parent.classList.remove("show");
        }
        teletonChat.parent=container;
        container.appendChild(ifrm);
        container.classList.add("teleton-chat-container");
        sc.parentNode.insertBefore(container, sc);

        
        let cssId = 'teletonChatCss';
        if (!document.getElementById(cssId))
        {
            let head  = document.getElementsByTagName('head')[0];
            let link  = document.createElement('link');
            link.id   = cssId;
            link.rel  = 'stylesheet';
            link.type = 'text/css';
            link.href = '//teleton.me//chatWidget/css/outer.css';
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
            case "CWSESS":
                twcqw.cookie.set( "CWSESS" , dt.val);
            break;
        }

    }
    
}

document.addEventListener("DOMContentLoaded", ()=>{
    teletonChat.init(0);
});

if (window.addEventListener){
    window.addEventListener("message", teletonChat.lst,false);
}

var twcqw={
    qs:(e)=> {return twcqw.qsa(e)[0]},
    qsa:(e)=> {try {return document.querySelectorAll(e)} catch (v){return []} },
    gpars:(s)=> {let e; return (e = (s?s:document.URL).split("?")[1]) ? JSON.parse('{"' + e.replace(/&/g, '","').replace(/=/g, '":"') + '"}') : []},
    cookie:{
        ch:()=>{if(navigator.cookieEnabled === false) return console.warn('Cookies are disabled!');return 1;},
        set:(n, v, p=30)=>{ if(!twcqw.cookie.ch()) return;
            let d = new Date();
            d.setTime(d.getTime() + (p * 24 * 60 * 60 * 1000));
            document.cookie = n + "=" + v + "; expires=" + d.toGMTString() + "; path=/"; 
        },
        get:(n)=>{ if(!twcqw.cookie.ch()) return;
            let m = n + "=",a = document.cookie.split(';'),i,c;
            for (i = 0; i < a.length; i++)
                if ((c = a[i].trim()).indexOf(m) == 0) {
                    let f=c.substring(m.length, c.length);
                    return f
                };
            return "";
        },
        delete:(n)=>{ if(!twcqw.cookie.ch()) return;
            document.cookie = n+"=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
        }
    },
}