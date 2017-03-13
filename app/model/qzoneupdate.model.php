<?php
/**
 * Модель обновления рейтинга в соц.сети Qzone 
 */
class QzoneUpdateModel extends AbstractRatingUpdateModel{
    
    /**
     * Конструктор объекта
     * 
     * @param int $userID Идентификатор пользователя
     */
    public function __construct($userID) {
        parent::__construct($userID);
        $this->login();
    }
    
    /**
     * @TODO: Логинится в Qzone
     */
    private function login() {
        echo $this->curl->post('http://i.qq.com/', ['u' => 'svmatulis@gmail.com', 'p' => 'stas0n28'],[], 'text/html', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3', false, 1, 
                          [CURLOPT_POST => 1, CURLOPT_HEADER => 0, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => 1,
                           CURLOPT_HTTPHEADER => ['Accept-Charset: utf-8','Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3','Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'],
                           CURLOPT_COOKIEFILE => getcwd ().'/cookies/cook_q.txt',  CURLOPT_COOKIEJAR => getcwd ().'/cookies/cook_q.txt',
                           CURLOPT_RETURNTRANSFER => 1, CURLOPT_REFERER => 'http://qzone.com']);   
    }
    
    /**
     * @TODO: Парсинг количества подписчиков 
     */
    public function getCount() {
        ;
    }
}
