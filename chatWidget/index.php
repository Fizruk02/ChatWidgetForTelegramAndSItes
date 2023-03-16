<?php
include $_SERVER['DOCUMENT_ROOT'].'/admin/functions/db_connect.php';

$sessionId = $_GET['sess']??false;

function select($query, array $par = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute($par);
    } catch (PDOException $e) {
        return [];
    }

    $arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($arr, $row);
    }
    return $arr;
}

function query($query, array $par = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute($par);
    } catch (PDOException $e) {
    }
    return $pdo->lastInsertId();
}


$host = parse_url($_SERVER['HTTP_REFERER'])['host']??'';
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$error = false;
$errors = [
    'site_not_found'=> [
        'text'=> 'Добавьте сайт<br>'.$host.'</b> <br>в личном кабинете на сайте <a target="_blank" href="https://teleton.me">Teleton.me</a>',
        'img'=> ''
    ],
    'channel_id_is_empty'=> [
        'text'=> 'Добавьте id канала для сайта <br><b>'.$host.'</b> <br>в личном кабинете на сайте <a target="_blank" href="https://teleton.me">Teleton.me</a>
                        в разделе «Чат-виджет»',
        'img'=> ''
    ],


];

$hash = select("SELECT u.`hash` 'hash', u.`id` 'id' 
FROM `cw_users` `u` 
JOIN `cw_sites_users` `s` 
JOIN `www_sites` `ws` 
ON `u`.`id` = `s`.`user_id` 
WHERE `s`.`site_id` = `ws`.`id` 
AND `ws`.`host` = ? AND `u`.`useragent` = ?", [ $host, $user_agent])[0];

if(empty($hash)){
    $userHash = bin2hex(random_bytes(20));
    query('INSERT INTO `cw_users` (`hash`, `operator`, `name`, `ip`, `useragent`) VALUES (?, ?, ?, ?, ?)', [ $userHash, 0, 'SiteUser', $ip, $user_agent ]);
    $userId = select('SELECT `id` FROM `cw_users` WHERE `hash` = ?', [ $userHash ])[0]['id'];
    $siteId = select("SELECT `id` FROM `www_sites` WHERE `host` = ?", [ $host ])[0]['id'];
    query("INSERT INTO `cw_sites_users` (`site_id`, `user_id`) VALUES (?, ?)", [ $siteId, $userId ]);
}
else {
    $userHash = $hash['hash'];
    $userId = $hash['id'];
}


//echo $userHash;


?>

<html lang="ru">
<head>
    <meta charset="utf-8">
    <!--<meta name="viewport" content="width=device-width">-->
    <link href="css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>


<!--<div class="chat-container"></div>-->
<!--<span class="chat-connect"></span>-->
<div class="chat-error"></div>
<div class="chat-area">
    <?php if(!$error) { ?>
        <div id="chatBox" class="chat-box"> </div>
        <div class="button-line">
            <span class="chat-process"></span>
            <div class="bot-form">
                <div class="textarea">
                    <div contenteditable="true" class="bot-chat-text-send"></div>
                </div>
                <div class="bot-btn"> </div>
            </div>
        </div>

    <?php } else {?>

        <div id="chatBox" class="chat-box">
            <div class="bot-chat-msg right" style="">
                <div class="bot-chat-text">
                    <div class="chat-text">
                        <?php echo $errors[$error]['text']; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>

</div>


<?php if(!$error) { ?>
    <script>user={hash:"<?php echo $hash; ?>",oper:0,}</script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>

    <script>
        const constants = {
            sessId: "<?php echo $userHash; ?>",
            wss: "wss://admin-testchat.host2bot.ru/ws/chat",
        }
        window.parent.postMessage({ method: "CWSESS", val: constants.sessId }, '*');
    </script>


<?php }?>
<!--<img class="message-new" src="/files/new.png">-->


</body>
</html>














