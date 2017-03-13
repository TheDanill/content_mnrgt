<?php
/**
 * Модель авторизации в соц.сети Qzone
 */
class QzoneAuthModel extends SocialNetworkAuthModel {
    const APP_ID = 101300770,
            
          APP_KEY = '304be265d50f3f58874065a5257356fa';

            /*
             * @var string Токен авторизации
             */
    private $access_token,
            /*
             * @var string Идентификатор дял авторизации
             */
            $open_id,
            /*
             * @var array Массив с пользовательской информацией
             */
            $userInfo;
    /**
     * @var string Уникальный код авторизации 
     */
    public $code;
    /**
     * Конструктор объекта
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Авторизация и/или регистрация пользователя
     * 
     * @param string $code Код, полученный в ответе от qzone
     * 
     * @return boolean true - в случае успешной авторизации, false - при неудаче
     */
    public function authorization() {
        parent::authorization();
        parse_str($this->getAccessTokenInfo($this->code));
        $this->access_token = $access_token;
        $_SESSION['access_token'] = $access_token;
        $_SESSION['expires_in'] = $expires_in;
        $_SESSION['refresh_token'] = $refresh_token;
        $this->open_id = $this->getOpenId();
        $this->userInfo = $this->getUserInfo();
        if($this->isRegistered($this->userInfo['nickname'])){
           $_SESSION['info']['nickname'] = $this->userInfo['nickname'];
           return true;           
        }else{
            if($this->registration())
                return true;
        }
        return false;
        
    }
    
    /**
     * Проверяет зарегистрирован ли пользователь
     * 
     * @return mixed Результат запроса
     */
    protected function isRegistered($userID) {
        return DB::get()->select('SELECT `id` FROM `users` WHERE `qzone` = "'.$userID.'"');       
    }
    
    /**
     * Вносит пользователя в БД
     * 
     * @return mixed Результат запроса
     */
    protected function registration(){
        return DB::get()->query('INSERT INTO `users` SET `nickname` = "'.$this->userInfo['nickname'].'", `qzone` = "'.$this->userInfo['nickname'].'"');     
    }
    
    /**
     * Получение информации о пользователе при помощи API Qzone
     * 
     * @return array Массив с информацией
     */
    private function getUserInfo(){
       return $this->curl->get_json('https://graph.qq.com/user/get_user_info', ['access_token' => $this->access_token, 'oauth_consumer_key' => self::APP_ID, 'openid' => $this->open_id, 'format' => 'json']);
    }
    
    /**
     * Получение идентификатора авторизации при помощи API Qzone
     * 
     * @return string $openid Идентификатор
     */
    private function getOpenId() {
        preg_match('/"openid":".*?"/',$this->curl->get('https://graph.qq.com/oauth2.0/me',['access_token' => $this->access_token]), $m);
        $json = json_decode('{'.$m[0].'}');
        $openid = $json->openid;
        return $openid;
    }
    
    
    /**
     * Получение токена авторизации
     * 
     * @param string $code Код, полученный в ответе от Qzone
     * 
     * @return  string Строка с данными
     */
    private function getAccessTokenInfo($code){
        return $this->curl->get('https://graph.qq.com/oauth2.0/token', ['grant_type' => 'authorization_code', 'client_id' => self::APP_ID, 'client_secret' => self::APP_KEY, 'code' => $code, 'redirect_uri' => 'http://'.$GLOBALS['config']['domain']]);
    }
    
    /**
     * Получение ссылки авторизации
     * 
     * @return string Ссылка
     */
    public static function getAuthLink(){
        return 'https://graph.qq.com/oauth2.0/authorize?response_type=code&state=123456&client_id='.self::APP_ID.'&redirect_uri=http://'.$GLOBALS['config']['domain'].'/qzoneauth';
    }
}

