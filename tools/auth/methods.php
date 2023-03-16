<?php

class cl
{
    public function auth( $input ){
        
        $data = json_decode($input['dt'],1);
        $host = $input['host'];

        $check = $this->check( $data );
        
        if(!$check['success']) return $check;
        
        $user_id = (int) $data['id'];
        
        $date = date('Y-m-d H:i:s', $data['auth_date']);
        
        query('DELETE FROM `auth_data` WHERE user_id='.$user_id.' AND host=?', [ $host ]);
        
        $id = query('INSERT INTO `auth_data` (`user_id`, `auth_date`, `first_name`, `username`, `hash`, `photo_url`, `host`) VALUES ('.$user_id.',?,?,?,?,?,?)',
                    [ $date, $data['first_name'], $data['username']??'', $data['hash'], $data['photo_url']??'', $host ]);
        
        if(!$id) return bterr('recording_error', 1505, 'Ошибка записи');
        
        return [ 
            'success'=> 'ok',
            'ssid'=> $data['hash']
        ];
    }
    
    private function check($auth_data) {
        $BOT_TOKEN = '5661623456:AAGerFp_biCJZM61ny78frxAcq2gFJ3_s6w';
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
          $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $BOT_TOKEN, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($hash, $check_hash) !== 0) {
          return bterr('invalid_data', 1503, 'Данные не валидны');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
          return bterr('data_is_outdated', 1504, 'Данные устарели');
        }
        return [ 'success'=> true ];
    }
}