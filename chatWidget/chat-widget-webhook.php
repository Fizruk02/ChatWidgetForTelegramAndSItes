<?php

require __DIR__ . '/app/vendor/autoload.php';

$json = file_get_contents('php://input');

if(!$json) exit;
foreach(['Db', 'Models', 'TG'] as $s) require __DIR__. '/app/'.$s.'.php'; //,  'TG'


use app\Db;
use app\TG;

ini_set('display_errors', true);
$obj = json_decode($json, true);

$tg = new TG;
$db = new Db;
$db-> connect();


class loadFiles extends Db
{
    private $botToken = "6154617593:AAFIJ1d2bVnHUO89EggALb9UEsj4sfPG-0M";
    private $apiUrl = "https://api.telegram.org/bot";

    public function __construct(){ $this->connect(); }
    private function getPhotoPath($file_id) {

        $array = json_decode($this->requestToTelegram(['file_id' => $file_id], "getFile"), TRUE);
        return  $array['result']['file_path'];
    }
    private function copyFile($file_path, $save_dir, $file_name) {

        # ссылка на файл в телеграме
        $file_from_tgrm = "https://api.telegram.org/file/bot".$this->botToken."/".$file_path;
        # достаем расширение файла
        $ext =  end(explode(".", $file_path));
        $link = "$save_dir/$file_name.$ext";
        # назначаем свое имя здесь $file_name.расширение_файла
        if(copy($file_from_tgrm, $link))
            return "$file_name.$ext";
        else
            return '';
    }
    private function requestToTelegram($data, $type)
    {
        $result = null;

        if (is_array($data)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $this->botToken . '/' . $type);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }


    public function saveVideo($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $video = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$video)
            return false;

        $video='files/documents/'.$video;
        $insertId = $this->insert('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "video", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $video]);

        return [ 'file'=> $video ,'id_file'=> $insertId ];
    }
    public function saveDocument($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $doc = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$doc)
            return false;

        $doc='files/documents/'.$doc;
        $insertId = $this->insert('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "doc", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $doc]);

        return [ 'file'=> $doc ,'id_file'=> $insertId ];
    }
    public function savePhoto($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла

        $small = $this->copyFile($this->getPhotoPath($data[0]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'small_'.$name);
        $medium = $this->copyFile($this->getPhotoPath($data[1]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'medium_'.$name);
        if($data[2])
            $large = $this->copyFile($this->getPhotoPath($data[2]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'large_'.$name);
        else
            $large = $medium;

        if(!$small && !$medium && !$large)
            return false;

        if(!$medium) $medium = $large?$large:$small;
        if(!$large) $large = $medium?$medium:$small;
        $small='files/images/'.$small;
        $medium='files/images/'.$medium;
        $large='files/images/'.$large;
        $insertId = $this->insert('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "img", :small_size, :medium_size, :large_size, -1)', [ ':id_file'=> $id_file, ':small_size'=> $small, ':medium_size'=> $medium, ':large_size'=> $large ]);

        return [ 'file'=> $medium ,'id_file'=> $insertId ];
    }
    public function getFileGroup() {
        return $this->single("SELECT IFNULL(max(id_group),0)+1 result FROM `files`")['result'];
    }
    public function getFilesforweb($id_group) {
        if(!$id_group||$id_group==="0"||$id_group==="false") return [];

        if($id_group=='all'){
            $arr=$this->select('SELECT * FROM `files` ORDER BY id DESC');
        } else {
            $arr=$this->select('SELECT * FROM `files` WHERE id_group = ?', [ $id_group ]);
        }

        return array_map(function($it) {
            $preview = $it['small_size']?:($it['medium_size']?:$it['large_size']);
            $file =    $it['large_size']?:($it['medium_size']?:$it['small_size']);
            $ext=strtolower(pathinfo($file, PATHINFO_EXTENSION));
            switch($ext){
                case 'mov':
                    $it['type_file'] = 'doc';
                    break;
            }
            return [
                'id_group'=> $it['id_group']
                ,'preview'=> strpos($preview, 'http')===false? '/'.$preview : $preview
                ,'file'=> strpos($file, 'http')===false? '/'.$file : $file
                ,'fileid'=> $it['id']
                ,'type'=> $it['type_file']
                ,'ext'=> $ext
                ,'lg'=> $it['large_size']
                ,'md'=> $it['medium_size']
                ,'sm'=> $it['small_size']
            ];
        }, $arr);
    }
}


$methods = array_merge(array_keys($obj),array_keys(is_array($obj['message'])?$obj['message']:[]));
/* ДОБАВИЛИ ИЛИ УДАЛИЛИ БОТА В ГРУППЕ */
if(in_array('my_chat_member', $methods)) {
        qwe($obj);
        $member=$obj['my_chat_member']['new_chat_member'];

        if($member['user']['id']!=BOT_ID()) exit;
        
        $chat=$obj['my_chat_member']['chat'];
        
        if($member['status']!='administrator') {
            $db-> update('DELETE FROM `_groups` WHERE group_id = '.$chat['id']  );
            exit;
        }

        $chatInfo = $tg->getChat($chat['id'])['result']??[];

        $chat['linked_chat_id'] = $chatInfo['linked_chat_id']??0;

        $from= $obj['my_chat_member']['from'];

        saveChat( $chat, $from );

        if($chat['type'] === 'channel') {
            
            $lch = $chatInfo['linked_chat_id']??false;
            if($lch && !getGroup( $lch )) {
                $text = 'Для работы чат-виджета теперь необходимо добавить меня в группу с обсуждениями';
                
                $tg-> sendMessage( [
                    'chat_id'=> $from['id'],
                    'text'=> $text,
                ]);
            }
        }
    

    return;
};





if(isset($obj['message']['is_automatic_forward'])) {
    
    $from_message_id=$obj['message']['forward_from_message_id'];
    $from_chat_id=$obj['message']['forward_from_chat']['id'];
    
    $chat_id=$obj['message']['chat']['id'];
    $message_id=$obj['message']['message_id'];
    
    $db-> update('UPDATE `cw_sessions` SET `comment_channel_id`=?, comment_message_id=? WHERE `channel_id`=? AND `message_id`=?',
                [ $chat_id, $message_id, $from_chat_id, $from_message_id ]);

}


if(isset($obj['callback_query'])) {
    $cb=json_decode($obj['callback_query']['data'],1);
    
    $dt=$obj['callback_query']['message']['reply_to_message'];

    $chat_id=$dt['forward_from_chat']['id'];
    $message_id=$dt['forward_from_message_id'];

    $user=$obj['callback_query']['from'];

    //$userId=getUserId( $user );
    $session=getSession($chat_id, $message_id);
    setStatus( $session );

    if(!$session) return;
    $userId = $db->single('SELECT `user_id` FROM `cw_sessions` WHERE `id` = ?', [ $session['id'] ])['user_id'];
    switch($cb['mtd']) {
        case 'getContacts':
            $p=[
                    'type' => 'command',
                    'mess' => 'getContacts',
                    'payload'=> [],
                    'sessionId'=> $session['id'],
                    'userId'=> $userId
                ];
            
        
            send( $p );
            $tg-> sendMessage( [
                'chat_id'=> $session['comment_channel_id'],
                'text'=> 'Запрошены контакты',
                'reply_to_message_id'=> $session['comment_message_id']
            ] );
        break;
        
        
        case 'setProccess':
            $p=[
                    'type' => 'notify',
                    'mess' => 'proccess',
                    'payload'=> [],
                    'sessionId'=> $session['id'],
                    'userId'=> $userId
                ];
            send( $p );
        break;
        
        
    }
    


    $tg->answerCallbackQuery('', $obj['callback_query']['id']);
}


if(isset($obj['message']['reply_to_message'])) {
    global $db;

    $lf = new loadFiles;

    $mess = !empty($obj['message']['caption']) ? $obj['message']['caption'] : $obj['message']['text'];

    $dt=$obj['message']['reply_to_message'];
    $chat_id=$dt['forward_from_chat']['id'];
    $message_id=$dt['forward_from_message_id'];

    $document = $obj['message']['document'] ?? [];
    $photo = $obj['message']['photo'] ?? [];
    $video = $obj['message']['video'] ?? [];
    $audio = $obj['message']['audio'] ?? [];
    $group = null;

    if (count($photo)) $save_info = $lf->savePhoto($photo, $photo[count($photo)-1]['file_id']);
    if (count($document)) $save_info = $lf->saveDocument($document, $document['file_id']);
    if (count($audio)) $save_info = $lf->saveDocument($audio, $audio['file_id']);
    if (count($video)) $save_info = $lf->saveVideo($video, $video['file_id']);

    $settings['files'][] = $save_info;

    if(!empty($save_info)){
        $group = $lf->getFileGroup();
        foreach ($settings['files'] as $fileItem) $db->update('UPDATE `files` SET `id_group` = :id_group  WHERE `id` = :id', [':id_group' => $group, ':id' => $fileItem['id_file']]);
    }



    //$user=$obj['message']['from'];
    //$userId=getUserId( $user );


    $session=getSession($chat_id, $message_id);
    setStatus( $session );
    if(!$session) return;

    $userId = $db->single('SELECT `user_id` FROM `cw_sessions` WHERE `id` = ?', [ $session['id'] ])['user_id'];
    $p=[
            'type' => 'tgmessage',
            'mess' => $mess,
            'groupFiles' => $group ?? 'nothing',
            'sessionId'=> $session['id'],
            'userId'=> $userId
        ];

    qwe($p);
    send( $p );

}

function send( $par ){
    try {
        
        \Ratchet\Client\connect('wss://admin-testchat.host2bot.ru/ws/chat')->then(function($conn) use($par) {
            $conn->send(json_encode($par));
            $conn->close();
        }, function ($e) {
            $msg = "Could not connect: {$e->getMessage()}\n";
            echo $msg;
        });

    } catch (\Exception $e) {
        file_put_contents(__DIR__.'/err.txt', "ERROR: ".$e->getMessage().PHP_EOL, FILE_APPEND);
        echo $e->getMessage().PHP_EOL;
    }
}

function getSession($chat_id, $message_id){
    global $db;
    return $db-> single('SELECT * FROM `cw_sessions` WHERE channel_id=? AND message_id=?', [ $chat_id, $message_id ]);
}

function setStatus( $session ){
    global $tg;
    $text='СЕССИЯ '.$session['id'].PHP_EOL.PHP_EOL.
    'Статус: ответ получен ✅️';
    
    $tg->edit_message($text, false, $session['channel_id'], $session['message_id']);
}

function getUserId( $user ){
    global $db;
    if(!$userData=$db-> single('SELECT * FROM `cw_users` WHERE telegram_id=?', [ $user['id'] ])){
        $userId = $db-> insert('INSERT INTO `cw_users` (`hash`, `operator`, `name`, `online`, `telegram_id`, `username`, `first_name`) VALUES ("",1,?,1,?,?,?)', 
            [ $user['first_name'], $user['id'], $user['username']??"", $user['first_name'] ]);
    } else {
        $userId=$userData['id'];
    }
    return $userId;
}

function qwe( $d, $f=false ){
    $arr=is_array($d);
    $d= $arr ? varexport($d):$d;

    $pre = '<html><head><meta charset="utf-8"></head>  <pre>'.($f?'<hr>':'').date('H:m:s').($arr?'<br>':' ').$d.'</pre>';
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.html', $pre, $f?FILE_APPEND:false);
}

function varexport($expression) {
    $export = var_export($expression, TRUE);
    $patterns = [
        "/array \(/" => '[',
        "/^([ ]*)\)(,?)$/m" => '$1]$2',
        "/=>[ ]?\n[ ]+\[/" => '=> [',
        "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
    ];
    $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
    return $export;
}

function saveChat( $chat, $responsible )
{
    global $db;
    if( getGroup( $chat['id'] ) ) exit;
	$db-> insert('INSERT INTO `_groups` (`group_id`, `title`, `username`, `type`, `admin_id`, `group_creator`, `linked_chat_id`) VALUES (?,?,?,?,?,?,?)',
	        [ $chat['id'], $chat['title'], $chat['username']??'', $chat['type'], $responsible['id'], $responsible['id'], $chat['linked_chat_id'] ]);

}

function getGroup( $group_id )
{
    global $db;
    $group_id = (int) $group_id;
    $gr=$db-> single('SELECT * FROM `_groups` WHERE group_id = '.$group_id  );
    if($gr) $gr['status']=1;
    
    return $gr;
}

function methods() {
    global $tg;
    return $tg;
}

function BOT_ID() {
    return 6154617593;
}