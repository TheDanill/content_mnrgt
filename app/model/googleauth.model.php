<?php
/**
 * Модель авторизации через сервисы Google
 */
class GoogleAuthModel extends SocialNetworkAuthModel {
           /**
            * @var integer Идентификатор приложения в Google.
            */
    const  CLIENT_ID = '728376655938-gusgrk7gsb72av56qgsqigf6pq37l6e4.apps.googleusercontent.com',
            
            /**
             * @var string Секретный ключ приложения Google.
             */
           CLIENT_SECRET_KEY = 'HpkxEab6-wR5CVSNGaeP-_u3',
            
            /**
             * @var string API KEY
             */
           API_KEY = 'AIzaSyBUaU4RDAyBM61R4WkffgNw2owJr95_SjM';
           
           /**
            * @var string Ключ доступа авторизации приложения.
            */
    public $code,
            
            /**
             * @var array Массив с данными о авторизации (access_token, expire, user_id).
             */
           $auth_info = [],
            
            /**
             *  @var array Массив с информацией о пользователе (заполняется с использованием API Instagram)
             */
           $user_info = [];
    


    /**
     * Конструктор авторизации через Google. 
     * 
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     *  Авторизация и внесение пользователя в БД.
     */
    public function authorization(){
        if(!AuthModel::get()->isAuthorized()){
                    $this->auth_info = $this->curl->post_json('https://accounts.google.com/o/oauth2/token',['code' => $this->code,
                                                                                     'client_id' => self::CLIENT_ID,
                                                                                     'client_secret' => self::CLIENT_SECRET_KEY,
                                                                                     'redirect_uri' => 'http://'.$GLOBALS['config']['domain'].'/googleauth',
                                                                                     'grant_type' => 'authorization_code']);
        }
        $this->user_info = $this->curl->get_json('https://www.googleapis.com/plus/v1/people/me?access_token='.$this->auth_info['access_token'].'');
        parent::authorization();
        $_SESSION['name'] = $this->user_info['name']['givenName'];
        $_SESSION['surname'] = $this->user_info['name']['familyName'];
        $_SESSION['id'] = $this->user_info['id'];
        if($this->isRegistered($this->user_info['id'])){
            $this->updateUserInfo();
            $user = UserModel::get()->getInfoBySocId($this->user_info['id'], 'google');
            $_SESSION['info'] = $user;
            return true;
        }
        else{
            if($this->registration()) {
                $user = UserModel::get()->getInfoBySocId($this->user_info['id'], 'google');
                $_SESSION['info'] = $user;
                return true;
            }
        }      
    }
    
    /**
     * Проверяется регистрация у пользователя
     * 
     * @param int $userId Идентификатор в Google
     * 
     * @return boolean Результат выполнения операции
     */
    protected function isRegistered($userId){
        if(DB::get()->select('
            SELECT id FROM `users` WHERE `google` = "'.$userId.'"
            ', DB::ASSOC)){
            return true;
        }
        return false;
    } 
    
    /**
     *  Обновление информации о пользователе при входе
     * 
     * @return mixed Результат выполнения запроса
     */
    private function updateUserInfo(){
       $userData = $this->makeArrayUserInfo(); 
       $set = '';
       foreach($userData as $k => $v){
           $set .= '`'.$k.'`="'.$v.'", ';
       }
       $set = substr(trim($set),0,-1);
       $sql = 'UPDATE `users` SET '.$set.' WHERE google='.$this->user_info['id'];
       return DB::get()->query($sql);            
    }

    /**
     * Регистрация пользователя
     * 
     * @return mixed Результат выполнения запроса
     */
    protected function registration(){
        return UserModel::get()->add($this->makeArrayUserInfo());
    }
    
    /**
     * Формирование массива с пользовательской информацие для внесения в БД
     * 
     * @return mixed Массив с данными, либо false
     */
    private function makeArrayUserInfo(){
        
        $infoArr = [];
        
        $tr = new Translit();
        
        $infoArr['name'] = $this->user_info['name']['givenName'];
        $infoArr['surname'] = $this->user_info['name']['familyName'];
        $infoArr['name_en'] = $tr->cyrToLat($this->user_info['name']['givenName']);
        $infoArr['surname_en'] = $tr->cyrToLat($this->user_info['name']['familyName']);
        
        $infoArr['google'] = $this->user_info['id'];
        $infoArr['google_count'] = $this->user_info['circledByCount'];
        $infoArr['google_profile_url'] = 'https://plus.google.com/'.$this->user_info['id'];
        
        if (! empty( $infoArr ) ) {
            // language
            $infoArr['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));
            return $infoArr;
        }
        return false;
    }
    
    public static function getAuthLink(){
        return "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=".self::CLIENT_ID."&redirect_uri=http://".$GLOBALS['config']['domain']."/googleauth&scope=https://www.googleapis.com/auth/plus.me";
    }
    
}

