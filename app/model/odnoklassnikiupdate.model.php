<?php

/**
 * Модель обновления рейтинга для Odnoklassniki
 */
class OdnoklassnikiUpdateModel extends AbstractRatingUpdateModel{
    
    const NETWORK_NAME = 'odnoklassniki';
    
    /**
     * Конструктор объекта
     * 
     * @param int $userID Идентификатор пользователя в системе
     */
    public function __construct($userID) {
        parent::__construct($userID);
        $this->profileUrl = $this->getProfileUrl('odnoklassniki');
    }
    
    
    /**
     * Получение количества друзей путем парсинга
     * 
     * @return boolean true/false в зависимости от наличия результатов
     */
    public function getCount(){
        if(isset($this->profileUrl)){
            $page = $this->curl->get($this->profileUrl);
            preg_match('/<span class="navMenuCount">(.*?)<\/span>/', $page, $m);
            if(isset($m[1])){
                $this->count = $m[1];
                return true;
            }
        }
        return false;
    }
}

