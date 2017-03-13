<?php
/**
 * Модель авторизации в соц.сети Weibo
 */
class WeiboAuthModel extends SocialNetworkAuthModel {
    
    const APP_KEY = 4131119319,
          APP_SECRET = '9e3a3db3cc20f61fb94e0a34b38aaeb5';
    
    /**
     * @var array Информация для авторизации
     */
    private $accessTokenInfo,
            /**
             * @var array Массив пользовательской информации
             */
            $userInfo;
    
    /**
     * Конструктор объекта
     * @param int $code Уникальный код авторизации
     */
    public function __construct($code) {
        parent::__construct();
        $this->accessTokenInfo = $this->getAccessToken($code);
        $_SESSION['access_token'] = $this->accessTokenInfo['access_token'];
        $_SESSION['expires_in'] = $this->accessTokenInfo['expires_in'];
        $this->userInfo = $this->getUserInfo();
        $this->authorization();
    }
    
    
    /**
     * Авторизация и регистрация пользователя
     * @return boolean Результат операции
     */
    public function authorization() {
        parent::authorization();
        if(isset($this->userInfo['id'])){
            if($this->isRegistered($this->userInfo['id'])){
                $_SESSION['info']['id'] = $this->userInfo['id'];
                $_SESSION['info']['nickname'] = $this->userInfo['name'];
                if($this->updateInfo($this->getUserInfoArray())){
                    return true;
                }
            }
            else{
                if($this->registration()){
                    $_SESSION['info']['id'] = $this->userInfo['id'];
                    $_SESSION['info']['nickname'] = $this->userInfo['name'];
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Обновление информации о пользователе в БД при очередной авторизации
     * @param array $info Массив информации пользователя
     * @return mixed Результат выполнения запроса
     */
    private function updateInfo($info){
        $set = '';
        foreach($info as $k => $v){
            $set .= '`'.$k.'` = "'.$v.'", ';
        }
        $set = substr(trim($set), 0, -1);
        return DB::get()->query('UPDATE `users` SET'.$set.' WHERE `weibo` = '.$info['weibo']);
    }

    /**
     * Регистрация пользователя Weibo в системе
     * @return mixed Результат выполнения запроса
     */
    protected function registration(){
        return UserModel::get()->add($this->getUserInfoArray());
    }
    
    /**
     * Формирование массива с пользовательской информацией для добавление и обновления в БД
     * @return array Сформированный массив
     */
    private function getUserInfoArray(){
        $infoArray = [];
        $infoArray['weibo'] = $this->userInfo['id'];
        $infoArray['nickname'] = $this->userInfo['name'];
        $infoArray['weibo_count'] = $this->userInfo['followers_count'];
        $infoArray['weibo_profile_url'] = 'http://weibo.com/'.$this->userInfo['profile_url'];
        $infoArray['weibo_token'] = $this->accessTokenInfo['access_token'];
        $infoArray['default_lang'] = 4;
        return $infoArray;
    }
    
    /**
     * Проверяет - зарегистрирован ли пользователь в системеы
     * @param integer $weiboid Идентификатор пользователь в Weibo
     * @return mixed Результат выполнения запроса
     */
    protected function isRegistered($weiboid) {
        return DB::get()->select('SELECT `id` FROM `users` WHERE `weibo` = '.$weiboid);        
    }

    /**
     * Достает информацию о пользователе через API Weibo
     * @return array Массив с информацией о пользователе
     */
    private function getUserInfo(){
        return $this->curl->get_json('https://api.weibo.com/2/users/show.json', ['source' => self::APP_KEY, 'uid' => $this->accessTokenInfo['uid'], 'access_token' => $this->accessTokenInfo['access_token']]);
    }
    
    /**
     * Получение информации для авторизации в т.ч. access token
     * @return array Массив с информацией об авторизации
     */
    private function getAccessToken($code){
        return $this->curl->post_json('https://api.weibo.com/oauth2/access_token', ['client_id' => self::APP_KEY, 'client_secret' => self::APP_SECRET, 'grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => 'http://'.$GLOBALS['config']['domain'].'/weiboauth']);     
    }

    /**
     *  Генерирует ссылку авторизации для страницы авторизации
     *  @return string Ссылка
     */
    public static function getAuthLink(){
        return 'https://api.weibo.com/oauth2/authorize?client_id='.self::APP_KEY.'&redirect_uri=http://'.$GLOBALS['config']['domain'].'/weiboauth&response_type=code';
    }
}

