<?php

/**
 * Модель обновления рейтинга LinkedIn 
 */
class LinkedInUpdateModel extends AbstractRatingUpdateModel{
    const NETWORK_NAME = 'linkedin';
    
    
    /**
     * Конструктор класса
     */
    public function __construct($userID) {
        parent::__construct($userID);
        $this->profileUrl = $this->getProfileUrl(self::NETWORK_NAME);
    }
    
    /**
     * Получение количества подписчиков из аккаунта в LinkedIn путем парсинга страницы пользователя
     * 
     * @return boolean Результат выполнения операции
     */
    public function getCount(){
        if(isset($this->profileUrl)){
            $page = $this->curl->get($this->profileUrl);
            preg_match_all('|<div class="member-connections"><strong>(.*?)<\/strong>|', $page, $matches);
            $this->count = $matches[1][0];
            return true;           
        }
        return false;
    }
}

