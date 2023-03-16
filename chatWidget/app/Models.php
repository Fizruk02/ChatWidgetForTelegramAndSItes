<?php
namespace app;

class Models extends Db
{
    protected function _setSession( $userId ){
        return $this->insert(
            'INSERT INTO `cw_sessions` (`user_id`, `date`, `status`) VALUES(?, now(), 0)', [ $userId ]
            );
    }
    
    protected function _setSessionChannel( $channel_id, $message_id, $id ){
        return $this->update(
            'UPDATE `cw_sessions` SET `channel_id`=?, `message_id`=?  WHERE id=?', [ $channel_id, $message_id, $id ]
            );
    }
    
    protected function _getOpenUserSession( $userId ){
        return $this->single(
            'SELECT * FROM `cw_sessions` WHERE `user_id`=? AND `status`=0 ORDER BY `id` DESC LIMIT 1', [ $userId ]
            );
    }
    
    protected function _getLastOpenSession(){
        return $this->single(
            'SELECT * FROM `cw_sessions` WHERE status=0 ORDER BY id DESC LIMIT 1', [ ]
            );
    }
    
    protected function _getSession( $id ){
        return $this->single(
            'SELECT * FROM `cw_sessions` WHERE id=?', [ $id ]
            );
    }
    
    protected function _getSessionCommentMessId( $id ){
        return $this->single(
            'SELECT * FROM `cw_sessions` WHERE id=? AND comment_message_id', [ $id ]
            );
    }
    
    protected function _getUser( $hash ){
        return $this->single(
            'SELECT * FROM `cw_users` WHERE `hash`=?', [ $hash ]
            );
    }
    
    protected function _getUserById( $id ){
        return $this->single(
            'SELECT * FROM `cw_users` WHERE `id`=?', [ $id ]
            );
    }
    
    protected function _setUser( $hash, $oper, $name ){
        return $this->insert(
            'INSERT INTO `cw_users` (`date`, `hash`, `operator`, `name`) VALUES (now(), ?, ?, ?)', [ $hash, $oper, $name ]
            );
    }
    
    protected function _getMessages( $sessionId ){
        return $this->select(
            'SELECT * FROM `cw_messages` WHERE `session_id`=?', [ $sessionId ]
            );
    }
    
    protected function _setMess( $userId, $sessionId, $mess ){
        return $this->insert(
            'INSERT INTO `cw_messages` (`date`, `user_id`, `session_id`, `message`) VALUES (now(), ?, ?, ?)', [ $userId, $sessionId, $mess ]
            );
    }
    
    protected function _setOnline( $userId, $status ){
        return $this->update(
            'UPDATE `cw_users` SET `online`='.($status?'true':'false').' WHERE id=?', [ $userId ]
            );
    }

    protected function _getChannelId($userId){
        return $this->single(
            "SELECT g.`group_id`, `g`.`linked_chat_id` 
FROM `_groups` `g` 
JOIN `www_sites` `ws` 
JOIN `cw_sites_users` `su` 
ON `ws`.`chagroup_id` = `g`.`id` 
WHERE `su`.`user_id` = ?  
AND `su`.`site_id` = `ws`.`id`", [ $userId ]
        );
    }

    protected function _getHashFromSession($sessionId){
        return $this->select(
            "SELECT `cu`.`hash` FROM `cw_users` `cu` 
JOIN `cw_sites_users` `csu` 
JOIN `www_sites` `ws` 
JOIN `_groups` `g` 
JOIN `cw_sessions` `cs` 
ON `cs`.`channel_id` = `g`.`group_id` 
WHERE `ws`.`chagroup_id` = `g`.`id` 
AND `ws`.`id` = `csu`.`site_id` 
AND `csu`.`user_id` = `cu`.`id` 
AND `cs`.`id` = ?", [ $sessionId ]
        );
    }

    protected function _getHostName($sessionId){
        return $this->select(
            "SELECT `ws`.`host` FROM `www_sites` `ws` 
JOIN `cw_sessions` `cs` 
JOIN `cw_sites_users` `csu` 
ON `csu`.`site_id` = `ws`.`id` 
WHERE `csu`.`user_id` = `cs`.`user_id` 
AND `cs`.`id` = ?", [ $sessionId ]
        )[0];
    }

}