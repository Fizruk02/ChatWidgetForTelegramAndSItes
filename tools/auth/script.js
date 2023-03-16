var auth={
    activePopup:false,
    botId:5661623456,
    paramsEncoded:"origin=https%3A%2F%2Fteleton.me",
    host:"https://oauth.telegram.org",
    data:constants.authData,
    get:()=> {
        if(!auth.data.hash){
            let width = 550, height = 470,
            left = Math.max(0, (screen.width - width) / 2) + (screen.availLeft | 0), top = Math.max(0, (screen.height - height) / 2) + (screen.availTop | 0);
            auth.activePopup = window.open(auth.host+'/auth?bot_id='+auth.botId+"&origin=https%3A%2F%2Fteleton.me&request_access=write", 'telegram_oauth', 'width=' + width + ',height='+ height +',left=' + left + ',top=' + top + ',status=0,location=0,menubar=0,toolbar=0');
            auth.authFinished = false;
            auth.activePopup&&auth.activePopup.focus();
        } else {
            auth.post( "teletonAuth", auth.data );
        }

    },
    res:d=>{
        qw.post('/tools/auth/p.php?q=auth', {dt:d,host:constants.origin}, r=>{
            localStorage.setItem('stelt_ssid', r.ssid);
            let p=qw.gpars();
            p["stelt_ssid"]=r.ssid;
            auth.post( "teletonAuthSsid", r.ssid );
            //document.location.search='?'+auth.gpar(p);
            //auth.post( "teletonAuth", auth.data );
            
            auth.post( "teletonAuth", d );
            
            //document.location.reload();
        },"json")
    },
    size:()=>{
        let m=0,l=0; document.body.childNodes.forEach(it=>{
            if((t=it.offsetTop)!==undefined&&(h=(it.offsetHeight+t))>m) m=h;
            if((r=it.offsetLeft)!==undefined&&(w=(it.offsetWidth+r))>l) l=w;
            
        });
        auth.post( "teletonAuthResize", {h:m,w:l+1} );
    },
    gpar:a=>{
        var l=[];
        for(i in a) l.push(i+"="+a[i]);
        return l.join("&");
    },
    post:(e,r)=>{
        window.parent.postMessage({ event:e, result:r }, '*');
    }
}
window.addEventListener("message", e=> {
  if (e.origin===auth.host) {
      let d=JSON.parse(e.data);
      if(d.event==='auth_result') return auth.res(d.result);
      console.log(d.event);
  }
});

auth.size();