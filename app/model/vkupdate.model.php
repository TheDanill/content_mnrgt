<?php
/**
 * Модель обновления рейтинга Вконтакте
 */
class VkUpdateModel extends AbstractRatingUpdateModel {
    const NETWORK_NAME = 'vkontakte';
    
    public $count;
    
    /**
     * Конструктор объекта
     * @param integer $userID 
     */
    public function __construct($userID) {
        parent::__construct($userID);
        if($vkid = $this->getVkId()){
            $this->vkID = $vkid[0]['vkontakte'];
        }
    }
    
    /**
     * Получение количества друзей и подписчиков пользователя в ВК
     * @return boolean 
     */
    public function getCount(){
        if (isset($this->vkID)) {
            $arr = [];
            
            if ($followersCount = $this->getUserCountFollowers()) {
                $arr['vkontakte_followers_count'] = $followersCount;   
            } else {
                $arr['vkontakte_followers_count'] = 0;
            }
            
            if ($friendsCount = $this->getUserCountFriends()) {
                $arr['vkontakte_friends_count'] = $friendsCount;
            } else {
                $arr['vkontakte_friends_count'] = 0;
            }
            
            $this->count = $arr;
            return true;            
        }
        return false;
    }
    
    /**
     * Получение идентификатора пользователя в ВК
     * @return array Массив с идентификатором
     */
    private function getVkId() {
        return DB::get()->select('SELECT `vkontakte` FROM `users` WHERE `id`='.$this->userID, DB::ASSOC);
    }
    
    /**
     * Получение количества подписчиков, используя API Вконтакте
     * @return integer Количество подписчиков
     */
    private function getUserCountFollowers() {
         $arr = $this->curl->get_json('https://api.vk.com/method/users.getFollowers?user_id='.$this->vkID);
         return $arr['response']['count'];
    }

    /**
     * Получение количества друзей, используя API Вконтакте
     * @return integer Количество друзей
     */
    private function getUserCountFriends() {
        $arr = $this->curl->get_json('https://api.vk.com/method/friends.get?user_id='.$this->vkID);
        return count($arr['response']);
    }
}

