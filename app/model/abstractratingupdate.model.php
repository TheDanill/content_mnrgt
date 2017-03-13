<?php
/**
 * @abstract
 * Модель обновления рейтинга из соц.сетей
 */
abstract class AbstractRatingUpdateModel {
    
    /**
     * @var int Идентификатор пользователя
     */
    protected $userID,
            /*
             * @var object Работа с HTTP запросами
             */
              $curl,
            /*
             * @var string Адрес страницы пользователя в соц.сети
             */
              $profileUrl;
        /*
         * @var int Значение рейтинга
         */
    public $count;

    /**
     * Конструктор объекта
     * 
     * @param $userId Идентификатор пользователя в системе 
     */
    public function __construct($userID) {
        $this->userID = $userID;
        $this->curl = new HTTP();
    }
    
    /**
     * Получение адреса аккаунта соц.сети пользователя
     * 
     * @param string $network Название необходимой соц.сети
     * 
     * @return string Адрес страницы
     */
    protected function getProfileUrl($network) {
        $array = DB::get()->select('SELECT `'.$network.'_profile_url` FROM `users` WHERE id='.$this->userID, DB::ASSOC);
        return $array[0][$network.'_profile_url'];
    }
    
    /**
     * @abstract
     * Получение рейтинга 
     */
    abstract protected function getCount();
    
}

