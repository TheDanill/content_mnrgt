<?php

/**
 * Модель авторизации через LinkedIn
 */
class LinkedInAuthModel extends SocialNetworkAuthModel {
    
    const CLIENT_ID = '771i95gbxabomw',
          SECRET_KEY = 'p1ZiD2G5MOYr32Im',
          STATE = 'mL4qMux3';  
    
    /**
     * @var string Код авторизации
     */
    public $code;
    
    /**
     * @var object Объект для HTTP запросов 
     */
            /**
             * @var array Информация авторизации
             */
    private $accessInfoArray = [],
            
            /**
             * @var array Информация о пользователе
             */
            $userInfoArray = [];

    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Авторизация пользователя
     * 
     * @return boolean Результат выполнения операции
     */
    public function authorization() {
        parent::authorization();
        if($this->isAuthorized()) {
            return true;
        }else{
            $this->accessInfoArray = $this->curl->post_json('https://www.linkedin.com/uas/oauth2/accessToken', [
                                                                                                                 'grant_type' => 'authorization_code',
                                                                                                                 'code' => $this->code,
                                                                                                                 'redirect_uri' => 'http://'.$GLOBALS['config']['domain'].'/linkedauth',
                                                                                                                 'client_id' => self::CLIENT_ID,
                                                                                                                 'client_secret' => self::SECRET_KEY
                                                                                                               ]);   
            $_SESSION['access_token'] = $this->accessInfoArray['access_token'];
            $_SESSION['expires_in'] = $this->accessInfoArray['expires_in'];
            $this->getUserInfo();
            if($this->isRegistered($this->userInfoArray['id'])){
                $this->updateUserInfo();
                $_SESSION['info']['surname'] = $this->userInfoArray['lastName'];
                $_SESSION['info']['name'] = $this->userInfoArray['firstName'];
                $_SESSION['info']['id'] = $this->userInfoArray['id'];
                return true;
            }else{
                if($this->registration()){
                    $_SESSION['info']['surname'] = $this->userInfoArray['lastName'];
                    $_SESSION['info']['name'] = $this->userInfoArray['firstName'];
                    $_SESSION['info']['id'] = $this->userInfoArray['id'];
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Обновление данных пользователя в БД при авторизации
     * 
     * @return mixed Резудьтат выполнения запроса
     */
    private function updateUserInfo(){
        $userData = $this->formUserInfoArray(); 
        $set = '';
        foreach($userData as $k => $v){
            $set .= '`'.$k.'`="'.$v.'", ';
        }
        $set = substr(trim($set),0,-1);
        $sql = 'UPDATE `users` SET '.$set.' WHERE linkedin="'.$this->userInfoArray['id'].'"';
        return DB::get()->query($sql);
    }
    
    /**
     * Проверка зарегистрирован ли пользователь
     * 
     * @return mixed Результат выполнения запроса
     */
    protected function isRegistered($userID){
        return DB::get()->select('SELECT `id` FROM `users` WHERE `linkedin` = "'.$userID.'"');
    }

    /**
     * Регистрация пользователя
     * 
     * @return int Идентификатор добавленного пользователя
     */
    protected function registration(){
        return UserModel::get()->add($this->formUserInfoArray());    
    }
    
    /**
     * Получение информации о пользователе путем парсинга
     */
    private function getUserInfo(){
        $this->userInfoArray = $this->curl->get_json('https://api.linkedin.com/v1/people/~:(id,first-name,last-name,location,public-profile-url)', ['format' => 'json'], 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', false, 1, [CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$this->accessInfoArray['access_token']]]);
        $page = file_get_contents($this->userInfoArray['publicProfileUrl']);
        preg_match_all('|<div class="member-connections"><strong>(.*?)<\/strong>|', $page, $matches);
        $this->userInfoArray['linkedin_count'] = intval($matches[1][0]);
    }

    /**
     * Формирование массива пользовательской информации, пригодного для добавления или обновления в БД
     * 
     * @return array Массив с данными пользователя (Имя, фамилия, id)
     */
    private function formUserInfoArray(){
        $userData = [];
        $tr = new Translit();
        $userData['linkedin'] = $this->userInfoArray['id'];
        $userData['name'] = $this->userInfoArray['firstName'];
        $userData['surname'] = $this->userInfoArray['lastName'];
        $userData['linkedin_count'] = $this->userInfoArray['linkedin_count'];
        $userData['linkedin_profile_url'] = $this->userInfoArray['publicProfileUrl'];
        if($tr->itCyrillic($this->userInfoArray['firstName']))
            $userData['name_en'] = $tr->cyrToLat($this->userInfoArray['firstName']);
        else
             $userData['name_en'] = $this->userInfoArray['firstName'];
        if($tr->itCyrillic($this->userInfoArray['lastName']))
            $userData['surname_en'] = $tr->cyrToLat($this->userInfoArray['lastName']);
        else
             $userData['surname_en'] = $this->userInfoArray['lastName'];
        return $userData;
    }

    public static function getAuthLink(){
        return "https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id=".self::CLIENT_ID."&redirect_uri=http://".$GLOBALS['config']['domain']."/linkedauth&state=".self::STATE;
    }
}

