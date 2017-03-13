<?php
    
    /**
     * Модель обновления рейтинга в Mail.Ru
     */
    class MailruUpdateModel extends AbstractRatingUpdateModel{
        
        const NETWORK_NAME = 'mailru';
       
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
         * Получение количества друзей путем парсинга
         * 
         * @param return boolean true/false в зависимости от наличия результата
         */
        public function getCount() {
            if(isset($this->profileUrl)){
                $page = $this->curl->get($this->profileUrl);
                preg_match_all('/Friends<span class="profile__menuLinkCounter">(.*?)<\/span>/', $page, $m);
                if(isset($m[1][0])){
                    $this->count = $m[1][0];
                    return true;
                }
            }
           return false;
        }
    }
