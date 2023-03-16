<?php
namespace app;

class Session extends Models
{
    public $user;
    public $tg;
    public $session;
    public $channel;
    public $sessionId;
    
    public function start( $hash, $oper ){
        $this->connect();
        $this->tg = new TG;

        $this->user=$this->getUser( $hash, $oper );


        if(!$this->user) return;
        if($this->user['operator']) {
            $this-> sessionId = $this->_getLastOpenSession()['id'];
            //$this-> channel = [
            //    'id'=> $this-> session['channel_id'],
            //    'message'=> $this-> session['message_id']
            //    ];
        }
        else {
            if($userSession=$this->_getOpenUserSession($this->user['id']))
                $this->sessionId = $userSession['id'];
            else
                $this->sessionId = $this->_setSession($this->user['id']);
        }
        $this->session = $this->_getSession( $this-> sessionId );
    }
    
    public function startById( $sessionId, $userId ){
        $this-> sessionId = $sessionId;
        $this-> session = $this->_getSession( $sessionId );
        $this->user=$this->_getUserById ($userId);
    }
    
    private function sendChannelMess( $mess ){
        $message_id=$this->session['message_id'];
        $channel_id=$this->_getChannelId($this->user['id']);
        $new=false;
        if(!$message_id){
            $new=true;

            $text='СЕССИЯ '.$this->sessionId.PHP_EOL.PHP_EOL.
            'Статус: ожидает ответа ❗️';



            $resp=$this->tg->sendMessage( [
                'chat_id'=> (int)$channel_id['group_id'],
                'text'=> $text
            ] );
            
            if(isset($resp['ok'])&$resp['ok']){
                $message_id=$resp['result']['message_id'];
                $channel_id=$resp['result']['chat']['id'];
                $this->_setSessionChannel( $channel_id, $message_id, $this->sessionId );
            }
            
        }

        $x=0;
        while(!$s=$this->_getSessionCommentMessId( $this->sessionId )) {
            if(!$x) echo '['.date("Y-m-d H:i:s").'] CONNECT TO TG_CHAT'.PHP_EOL;
            sleep(1);
            echo '['.date("Y-m-d H:i:s").'] '.$x.PHP_EOL;
            if(++$x>20) return;
        }
        
        
        if($new)
            $this-> sendFirstComment($s);

        $kb=[];
        $kb[]= [ ['text' => 'Показать, что отвечаю', 'callback_data' => json_encode([ 'mtd'=>'setProccess', 'sid'=> $this->sessionId ])] ]; 
        $kb=["inline_keyboard"=>$kb];
        $resp=$this->tg->sendMessage( [
            'chat_id'=> $s['comment_channel_id'],
            'text'=> $mess,
            'reply_to_message_id'=> $s['comment_message_id'],
             'kb'=> $kb,
        ] );
        
        
    }
    
    
    
    private function sendFirstComment($s){
        $kb=[];
        $kb[]= [ ['text' => 'Запросить контактные данные', 'callback_data' => json_encode([ 'mtd'=>'getContacts', 'sid'=> $this->sessionId ])] ];
        $kb=["inline_keyboard"=>$kb];
        $this->tg->sendMessage( [
            'chat_id'=> $s['comment_channel_id'],
            'text'=> $this->_getHostName($this->sessionId)['host'].PHP_EOL.'Управление',
            'reply_to_message_id'=> $s['comment_message_id'],
            'kb'=> $kb,
        ] );
    }
    
    public function sendMess( $mess ){
        $this->sendChannelMess( $mess );
        $this->recordMess( $mess );
    }
    
    public function sendTyping( ){
        
        $chat_id=$this->session['comment_channel_id'];
        $message_id=$this->session['comment_message_id'];
        $this->tg->sendChatAction( 'typing', $chat_id, ['reply_to_message_id'=> $message_id,] );
    }



    public function recordMess( $mess ){
        $this->_setMess( $this->user['id'], $this->sessionId, $mess );
    }
    
    public function getMessages( ){
        $resp = $this->_getMessages(  $this->sessionId );
        $userId=$this->user['id'];
        $resp = array_map(function($it) use($userId) {
            $it['my']=$it['user_id']==$userId;
            return $it;
        }, $resp);
        return $resp;
    }
    
    public function online( $st ){
        $this->_setOnline( $this->user['id'], $st );
    }

    private function getUser( $hash, $oper ){
        
        if(!$us=$this->_getUser( $hash )) {
            $id=$this->_setUser( $hash, $oper, $oper?'Оператор':'Пользователь' );
    
            $us=[
                'id'=> $id,
                'hash'=> $hash,
                'operator'=> $oper,
            ];
        }
    
        return $us;
    }
    
    public function getHashFromSession($sessionId){
        return $this->_getHashFromSession($sessionId);
    }

    public function getUserHash($userId){
        return $this->_getUserById($userId)['hash'];
    }
    
}