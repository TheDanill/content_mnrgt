<?php

/**
 * Модель обновления рейтинга из Instagram
 */
class InstagramUpdateModel extends AbstractRatingUpdateModel {
    
    const NETWORK_NAME = 'instagram';
    
    /**
     * Конструктор объекта 
     * 
     * @param int $userID Идентификатор пользователя в системе
     */
    public function __construct($userID) {
        parent::__construct($userID);
        $this->profileUrl = $this->getProfileUrl(self::NETWORK_NAME);
    }
    
    /**
     * Получения рейтинга пользователя со страницы путем парсинга
     * 
     * @return boolean Результат выполнения операции
     */
    public function getCount() {
        if(isset($this->profileUrl)){
            $page = file_get_contents($this->profileUrl);
            preg_match_all('/<script type="text\/javascript">window._sharedData = (.*?);<\/script>/', $page, $matches);
            $json = json_decode(html_entity_decode($matches[1][0]), true);
            $this->count = $json['entry_data']['ProfilePage']['0']['user']['followed_by']['count'];
            return true;   
        }
        return false;
    }
}

