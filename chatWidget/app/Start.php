<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

ignore_user_abort(true);

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use app\Chat;

require __DIR__ . '/vendor/autoload.php';
foreach(['Chat', 'Db', 'Models', 'Session', 'TG'] as $s) require_once __DIR__ . '/'.$s.'.php';

$client = new Chat;

$app = new Ratchet\App('5.181.108.172', 5000, '0.0.0.0');
$app->route('/chat', $client, array('*'));
$app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
echo "START SOCKET SERVER".PHP_EOL;
$app->run();

/*
cd /var/www/html/bot/teleton.bot.host2bot.ru/public_html/chatWidget/app
php Start.php
*/