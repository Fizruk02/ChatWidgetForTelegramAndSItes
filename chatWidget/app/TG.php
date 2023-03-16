<?php

namespace app;

class TG {
    
    static $BOTKEY;
    static $HOST;

    
    public function __construct(){
        //self::$BOTKEY='5217959204:AAH6l2O_6tq8JZLo1qR2uCgSUJZetylmkko';
        self::$BOTKEY='6154617593:AAFIJ1d2bVnHUO89EggALb9UEsj4sfPG-0M';
        self::$HOST='https://api.telegram.org/bot';
    }

    public function getUserProfilePhotos($chat_id){
        return $this->tgpost('getUserProfilePhotos', [ 'user_id'=>$chat_id ]);
    }
    
    public function sendLocation($heading, $longitude, $latitude, $keyboard, $chat_id){
        $par = ['chat_id'=>$chat_id, 'longitude'=>$longitude, 'latitude'=>$latitude, 'heading' => $heading];
        if($keyboard) $par['reply_markup'] = json_encode($keyboard);
        return $this->tgpost('sendLocation', $par);
    }

    public function answerInlineQuery($par){
        return $this->tgpost('answerInlineQuery', $par);
    }

    public function approveChatJoinRequest($chat_id, $user_id){
        return $this->tgpost('approveChatJoinRequest', ['chat_id'=>$chat_id, 'user_id'=>$user_id]);
    }


    public function sendMessage($par=[/* chat_id, audio*/]){
        if(isset($par['kb'])&&$par['kb']) {
            $par['reply_markup']=json_encode($par['kb']);
            unset($par['kb']);
        }
        return $this->tgpost('sendMessage', $par);
    }

    public function sendDice($chat_id, $emoji){
        return $this->tgpost('sendDice', [ 'chat_id'=>$chat_id, 'emoji'=> $emoji ]);
    }

    public function sendPoll($obj){
        return $this->tgpost('sendPoll', $obj);
    }

    public function exportChatInviteLink($chat_id){
        return $this->tgpost('exportChatInviteLink', ['chat_id'=>$chat_id]);
    }

    public function sendAudio($par=[/* chat_id, audio*/]){
        return $this->tgpost('sendAudio', $par);
    }

    public function sendVoice($par=[/* chat_id, voice*/]){
        return $this->tgpost('sendVoice', $par);
    }

    public function sendPhoto($chat_id, $photo){
        return $this->tgpost('sendPhoto', ['chat_id'=> $chat_id, 'photo'=> $photo]);
    }

    public function sendDocument($chat_id, $doc, $caption=''){
        return $this->tgpost('sendDocument', ['chat_id'=> $chat_id, 'document'=> $doc, 'caption'=> $caption]);
    }

    public function getMe(){
        return $this->tgpost('getMe', []);
    }

    public function sendChatAction($action, $chat_id, $par=[]){
        $par['chat_id']=$chat_id;
        $par['action']=$action;
        return $this->tgpost('sendChatAction', $par);
    }

    public function setMyCommands($commands){
        $commands = json_encode($commands);
        return $this->tgpost('setMyCommands', [ 'commands'=> $commands ]);
    }

    public function editMessageMedia($chat_id, $id_message, $link, $caption='',$kb=false){
        $type= 'photo';
        if($link&&is_numeric($link)){
            $file=singleQuery('SELECT * FROM `files` WHERE id_group=?',[$link]);
            $link = $file['large_size'] ? : ($file['medium_size'] ?: $file['small_size']);
            if (strpos( $link, 'http')===false) $link = str_replace('https:', 'http:', _dir_)."/".$link;
            switch ($file['type_file']) {
                case 'img':
                    $type= 'photo';
                    break;
                case 'doc':
                    $type= 'document';
                    break;
                case 'video':
                    $type= 'video';
                    break;
            }
        }
        $media = ['type'=>$type, 'media'=>$link, 'caption'=> $caption];
        $media['parse_mode'] = strpos($caption,'</')?'html': '';
        $par = ['chat_id'=>$chat_id, 'message_id'=>$id_message, 'media'=>json_encode($media)];
        if($kb) $par['reply_markup']=json_encode($kb);
        return $this->tgpost('editMessageMedia', $par);
    }

    public function pinned_mess($chat_id, $id_message){
        return $this->tgpost('pinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }

    public function unpinned_mess($chat_id, $id_message){
        return $this->tgpost('unpinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }


    public function delete_mess($chat_id, $id_message){
        return $this->tgpost('deleteMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }



    public function editKb($par=[ /*'inline_message_id'=>, 'kb'=> */ ]){
        global $original;
        $p=['reply_markup'=>json_encode($par['kb'])];
        $id=$par['inline_message_id']??($original['callback_query']['inline_message_id']??'');
        if($id){
            $p['inline_message_id']=$id;
        } else {
            $p['message_id']=$par['message_id']??($original['callback_query']['message']['message_id']??'');
            $p['chat_id']=$par['chat_id']??($original['callback_query']['message']['chat']['id']??'');
        }
        return $this->tgpost('editMessageReplyMarkup', $p);
    }

    public function edit_inline_keyboard($chat_id, $message_id, $inline_keyboard){
        return $this->tgpost('editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode($inline_keyboard)]);
    }

    public function edit_message($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return $this->tgpost('editMessageText', $p);
    }

    public function edit_message_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return $this->tgpost('editMessageCaption', $p);
    }



    public function editMsg( $par=[ /*'text'=> , 'inline_message_id'=>, 'kb'=> */ ] ){
        global $original;
        $text=$par['text']??'';

        $p = ['caption'=>$text, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        $id=$par['inline_message_id']??false;
        if(!$id&&!$par['message_id']) $id=$original['callback_query']['inline_message_id']??false;
        if($id){
            $p['inline_message_id']=$id;
        } else {
            $p['message_id']=$par['message_id']??($original['callback_query']['message']['message_id']??'');
            $p['chat_id']=$par['chat_id']??($original['callback_query']['message']['chat']['id']??'');
        }
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($par['kb'])
            $p['reply_markup']=json_encode($par['kb']);

        $res = $this->tgpost('editMessageText', $p);
        if(!$res['ok'])
            $res = $this->tgpost('editMessageCaption', $p);
        return $res;
    }


    public function edit_message_text_or_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        $res = $this->tgpost('editMessageText', $p);
        if(!$res['ok'])
            $res= $this->tgpost('editMessageCaption', $p);
        return $res;
    }

    public function forward_message($from_chat_id, $message_id, $chat_id){
        return $this->tgpost('forwardMessage', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id]);
    }

    public function answerCallbackQuery($text, $id_callback_query, $show_alert = 1){
        return $this->tgpost('answerCallbackQuery', ['callback_query_id'=>$id_callback_query, 'text'=>$text, 'show_alert'=>$show_alert, 'cache_time'=>'0']);
    }

    public function delete_this_inline_keyboard(){ # удалить инлайн клавиатуру у текущего сообщения
        global $chat_id, $message_id;
        $kb=json_encode(["inline_keyboard"=>[[]]]);
        return $this->tgpost('editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>$kb]);
    }

    public function copyMessage($from_chat_id, $message_id, $chat_id, $keyboard = false, $par = []){
        $p = array_merge(['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id], $par);
        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        return $this->tgpost('copyMessage', $p);
    }
    
    
    /**************
     *** GROUPS ***
     **************/
    public function getChat($chat_id){
        return $this->tgpost('getChat', [ 'chat_id'=>$chat_id ]);
    }

    public function getChatAdministrators($chat_id){
        return $this->tgpost('getChatAdministrators', ['chat_id'=>$chat_id]);
    }

    public function getChatMemberCount( $chat_id ){
        return $this->tgpost('getChatMemberCount', [ 'chat_id'=>$chat_id ]);
    }

    public function unbanChatMember($chat_id, $user_id){
        return $this->tgpost('unbanChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ]);
    }

    public function banChatMember($chat_id, $user_id){
        return $this->tgpost('banChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ]);
    }

    public function getChatMember($chat_id, $user_id){
        return $this->tgpost('getChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ]);
    }
    
    
    /**************************************************/
    
    private function tgpost($mtd, $par){
        $url=self::$HOST.self::$BOTKEY.'/'.$mtd;
        
        $curl = curl_init( $url );
        
        curl_setopt_array(
            $curl,
            [
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POSTFIELDS => is_array($par)?http_build_query($par):$par,
            ]
        );

        $res = curl_exec($curl);
        curl_reset($curl);

        return json_decode( $res, 1 );
    }

    public function error($arr){
        if($arr['error_code'])
            notification($arr['description'].PHP_EOL.'error_code: '.$arr['error_code']);
    }
}