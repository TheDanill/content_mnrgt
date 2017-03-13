<?php

class WeiboUpdateModel extends AbstractRatingUpdateModel{
    private $access_token,
            $weibo_id;
    
    public function __construct($userID) {
        parent::__construct($userID);
        $this->access_token = $this->getToken();
        $this->weibo_id = $this->getWeiboId();
    }
    private function getWeiboId(){
        $result = DB::get()->select('SELECT `weibo` FROM `users` WHERE `id` = '.$this->userID, DB::ASSOC);
        return $result[0]['weibo'];
    }
    private function getToken(){
        $result = DB::get()->select('SELECT `weibo_token` FROM `users` WHERE `id` = '.$this->userID, DB::ASSOC);
        return $result[0]['weibo_token'];
    }

    public function getCount() {
        $info = $this->curl->get_json('https://api.weibo.com/2/users/show.json', ['source' => WeiboAuthModel::APP_KEY, 'uid' => $this->weibo_id, 'access_token' => $this->access_token]);
        if(isset($info['followers_count'])){
            $this->count = $info['followers_count'];
            return true;
        }
        return false;
    }
}

