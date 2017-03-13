<?php
/**
 * Модель обновления рейтинга в Iwitter
 */
class TwitterUpdateModel extends AbstractRatingUpdateModel{
    const NETWORK_NAME = 'twitter';
    
    /**
     * Конструктор объекта 
     * 
     * @param integer $userID Идентификатор пользователя
     */
    public function __construct($userID) {
        parent::__construct($userID);
        $this->profileUrl = $this->getProfileUrl(self::NETWORK_NAME);
    }
    
    /**
     * Получение числа подписчиков из аккаунта путем парсинга
     * 
     * @return boolean True - в случае получения значения, false - в любом другом случае
     */
    public function getCount() {
        if(isset($this->profileUrl)){
            $page = $this->curl->get($this->profileUrl);
            preg_match('/<input type="hidden" id="init-data" class="json-data" value="(.*?)">/', $page, $m);
            $json = json_decode(html_entity_decode($m[1]),true);
            if($this->count = ['twitter_followers_count' => $json['profile_user']['followers_count'], 'twitter_friends_count' => $json['profile_user']['friends_count']])
                return true;   
        }
        return false;
    }
}