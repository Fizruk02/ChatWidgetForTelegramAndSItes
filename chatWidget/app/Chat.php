<?php
namespace app;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Отправляем сообщение всем подключенным клиентам, кроме отправителя
 */
class Chat implements MessageComponentInterface{
    protected $owner;
    protected $clients;
    protected $session;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->session = new Session;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->owner = $conn;
        $this->clients->attach( $conn );
        echo '['.date("Y-m-d H:i:s").'] CONNECT '.$conn->resourceId.PHP_EOL;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if (!$data) return;

        if(empty($this->getConnectionSession($from)) and isset($data->sessId)){
            $this->setConnectionSession($from, $data->sessId);
            echo '['.date("Y-m-d H:i:s").'] SET SESSION '.$this->getConnectionSession($from).PHP_EOL;
        }

        echo '['.date("Y-m-d H:i:s").'] ONMESSAGE'.PHP_EOL;

        $this->session->sessionId = $data->sessId;

        if(isset($data->sessId))
            $this->session->start( $data->sessId, $data->oper );

        if ($data->type == 'command')
        {
            foreach ($this->clients as $client) {
                if ($from == $client) {
                    $resp = [
                        'type' => $data->type,
                        'mess' => $data->mess,
                        'result' => true,
                    ];
                    $client->send(json_encode($resp));
                    
                    switch ($data->mess) {
                        case 'init':
                            $this->session->online( true );
                            
                            $respNtf = [
                                'type' => 'notify',
                                'mess' => 'connected',
                                'name'=> $this->session->user['name'],
                                'sessionId' => $data->sessionId,
                                'hash' => $this->session->getUserHash($data->userId),
                            ];
                            $this->sendForClient( $respNtf, $from );
                            
                            $client->send(json_encode([
                                'type' => 'command',
                                'mess' => 'loadHistory',
                                'messages'=> $this->session->getMessages(),
                                ]));
                            
                            break;

                            
                        case 'issueResolved':
                        case 'issueNotResolved':
                        case 'getContacts':
                        case 'endSession':

                            $respNtf = [
                                'type' => 'notify',
                                'mess' => $data->mess,
                                'hash' => $this->session->getUserHash($data->userId),
                                'payload' => $data->payload,
                                'name'=> $this->session->user['name'],
                            ];

                            $this->sendForClient( $respNtf, $from );

                            break;
                            
                        case 'sendFields':

                            //$respNtf = [
                            //    'type' => 'notify',
                            //    'mess' => $data->mess,
                            //    'payload' => $data->payload,
                            //    'name'=> $this->session->user['name'],
                            //];
                            
                            
                            $r=array_map(function($it){
                                return $it->id.': '.$it->val;
                            }, $data->payload);
                            
                            $t='КОНТАКТЫ:'.PHP_EOL.implode(PHP_EOL,$r);
                            
                            $this->session->sendMess( $t );

                            break;
                           

                            
                    }

                }
            }
        }
        elseif ($data->type == 'message') {
            $resp = [
                'type' => 'message',
                'mess' => $data->mess,
            ];
            $this->session->sendMess( $data->mess );
            $this->sendForClient($resp, $from);

        }
        elseif ($data->type == 'notify') {
            $resp = [
                'type' => $data->type,
                'mess' => 'proccess',
                'name'=> $this->session->user['name'],
            ];
            
            $this->session->sendTyping( );
            $this->sendForClient($resp, $from);
        }
        elseif ($data->type == 'tgmessage') {
            $respNtf = [
                'type' => 'notify',
                'mess' => 'connected',
                'name'=> $this->session->user['name'],
                'sessionId' => $data->sessionId,
                'hash' => $this->session->getUserHash($data->userId),
            ];
            $this->sendForClient($respNtf, $from);
            $resp = [
                'type' => 'message',
                'mess' => $data->mess,
                'sessionId' => $data->sessionId,
                'hash' => $this->session->getUserHash($data->userId),
                'groupFiles' => $data->groupFiles,
            ];

            $this->sendForClient($resp, $from);
            $this->session->recordMess($data->mess);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach( $conn );
        $this->session->online( false );
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
        //echo '['.date("Y-m-d H:i:s").'] ERROR'.PHP_EOL;
    }

    public function sendForClient($resp, $from){
        foreach ($this->clients as $client) {
            if($resp['hash'] == $this->getConnectionSession($client)){
                $client->send(json_encode($resp));
            }
        }
    }

    public function getConnectionSession(ConnectionInterface $conn){
        return $this->clients->offsetGet($conn);
    }

    public function setConnectionSession(ConnectionInterface $conn, $item){
        $this->clients->offsetSet($conn, $item);
    }

}
