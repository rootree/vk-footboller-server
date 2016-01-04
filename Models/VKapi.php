<?php
/**
 API Class Vkontakte.ru
 by Dostelon aka Rutrum
 icq: 577366
 http://devtown.ru/
 **/

class VKapi {

    const MESSAGE_LIMIT = 1024;
    const UIDS_LIMIT    = 100;

    function __construct($api_secret, $api_id, $speed_mailing) {
        $this->api_secret    = $api_secret;
        $this->api_id        = $api_id;
        $this->test_mode     = 1;
        $this->speed_mailing = $speed_mailing;
    }

    function getProfiles ($uids) {
        $request['fields'] = 'uid,first_name,last_name,nickname,sex,bdate(birthdate),city,country,timezone,photo,photo_medium,photo_big';
        $request['uids']   = $uids;
        $request['method'] = 'secure.getProfiles';
        return $this->request($request);
    }

    function sendNotification ($uids, $message) {

        if (mb_strlen($message) > VKapi::MESSAGE_LIMIT) {
            $message = mb_substr($message, 0, (VKapi::MESSAGE_LIMIT - 3)) . '...';
        }

        $request['message'] = $message;
        $request['method']  = 'secure.sendNotification';

        if (is_array($uids)) {

            if (count($uids) > VKapi::UIDS_LIMIT) {

                $countOfIteration = ceil(count($uids) / VKapi::UIDS_LIMIT);

                for ($index = 0; $index < $countOfIteration; $index++) {

                    $request['uids'] = implode(',', array_slice($uids, 0, VKapi::UIDS_LIMIT));
                    $this->request($request);

                    $uids = @array_slice($uids, VKapi::UIDS_LIMIT);

                }

            }else {

                $request['uids'] = implode(',', $uids);
                return $this->request($request);
            }

        }else {
            $request['uids'] = $uids;
            return $this->request($request);
        }

    }

    function saveAppStatus ($uid, $status) {
        $request['uid']    = $uid;
        $request['status'] = iconv('windows-1251', 'utf-8', $status);
        $request['method'] = 'secure.saveAppStatus';
        return $this->request($request);
    }

    function getAppStatus ($uid) {
        $request['uid']    = $uid;
        $request['method'] = 'secure.getAppStatus';
        return $this->request($request);
    }

    function getAppBalance () {
        $request['method'] = 'secure.getAppBalance';
        return $this->request($request);
    }

    function setStatus ($uid, $status) {
        $request['uid']    = $uid;
        $request['method'] = 'secure.saveAppStatus';
        $request['status'] = $status;
        return $this->request($request);
    }

    function getBalance ($uid) {
        $request['uid']    = $uid;
        $request['method'] = 'secure.getBalance';
        return $this->request($request);
    }

    function addVotes ($uid, $votes) {
        $request['uid']    = $uid;
        $request['votes']  = $votes;
        $request['method'] = 'secure.addVotes';
        return $this->request($request);
    }

    function withdrawVotes ($uid, $votes) {
        $request['uid']    = $uid;
        $request['votes']  = $votes;
        $request['method'] = 'secure.withdrawVotes';
        return $this->request($request);
    }

    function transferVotes ($uid_from, $uid_to, $votes) {
        $request['uid_from'] = $uid_from;
        $request['uid_to']   = $uid_to;
        $request['votes']    = $votes;
        $request['method']   = 'secure.transferVotes';
        return $this->request($request);
    }

    function getTransactionsHistory () {
        $request['method'] = 'secure.getTransactionsHistory';
        return $this->request($request);
    }

    function addRating ($uid, $rate) {
        $request['uid']    = $uid;
        $request['rate']   = $rate;
        $request['method'] = 'secure.addRating';
        return $this->request($request);
    }

    function setCounter ($uid, $counter) {
        $request['uid']     = $uid;
        $request['counter'] = $counter;
        $request['method']  = 'secure.setCounter';
        return $this->request($request);
    }

    function request($request) {
        $request['random']    = rand(100000,999999);
        $request['timestamp'] = time();
        $request['format']    = 'JSON';
        $request['api_id']    = $this->api_id;
        $request['v']    = '2.0';
	
        ksort($request);

        $str = NULL;

        foreach ($request as $key=>$value) {
            $str.=trim($key)."=".trim($value);
        }



        $request['sig'] = md5(trim($str.$this->api_secret));

        if(isset($request['message'])){
          //  $request['message'] = urlencode($request['message']);
        }

        $q = http_build_query($request);
		
		ini_set('allow_url_fopen', 'On');
		ini_set('allow_url_include', 'Off');

        $context = stream_context_create(array(
            'http' => array(
                'method'=>"POST",
                'timeout' => 3      // Timeout in seconds
            )
        ));

        if($context === false){
            return new ErrorPoint(ErrorPoint::CODE_VK, "Ошибка при запросе к соц.сети", ErrorPoint::TYPE_SYSTEM);
        }

		$url = "http://api.vkontakte.ru/api.php?".$q;
		$content = file_get_contents($url, 0, $context);
	    $result = json_decode($content,TRUE);
        
        // TODO: переделать в зависимости от кол-во получателей
        usleep($this->speed_mailing);

        return $result;
    }
}

?>