<?php

/**
 * Модель авторизации через Одноклассников
 */
class OdnoklAuthModel extends SocialNetworkAuthModel {

    const APP_ID = 1247785984,
            PUBLIC_KEY = 'CBAFLQFLEBABABABA',
            SECRET_KEY = 'C9BCDBAAB9A5525EF6CC4358',
            TOKEN_URL = 'http://api.odnoklassniki.ru/oauth/token.do',
            USER_INFO_URL = 'http://api.odnoklassniki.ru/fb.do';

    /**
     * @var string Response Odnoklassniki
     */
    private $code,
            /**
             * @var array Массив с информацией о авторизации
             */
            $access_token = [],
            /**
             * @var array Массив пользовательской информации
             */
            $user_info = [];

    /**
     * Конструктор объекта 
     * 
     * @param $code Response
     */
    public function __construct($code = '') {
        parent::__construct();
        $this->code = $code;
        $this->getAccessToken();
    }

    /**
     * Авторизация и/или регистрация пользователя в системе
     * 
     * @return mixed 
     */
    public function authorization() {
        parent::authorization();
        $_SESSION['id'] = $this->user_info['uid'];
        $_SESSION['name'] = $this->user_info['first_name'];
        $_SESSION['surname'] = $this->user_info['last_name'];
        // var_dump($this->user_info);
        if ($this->isRegistered($this->user_info['uid'])) {
            $user = UserModel::get()->getInfoBySocId($this->user_info['uid'], 'odnoklassniki');
            $_SESSION['info'] = $user;
            return $this->updateUserInfo();
        } else {
            if ($this->registration()) {
                $user = UserModel::get()->getInfoBySocId($this->user_info['uid'], 'odnoklassniki');
                $_SESSION['info'] = $user;
                return true;
            }
        }
    }

    /**
     * Обновление информациио пользователе
     * 
     * @return mixed Результат выполнения запроса
     */
    private function updateUserInfo() {
        $userData = $this->formUserInfoArray();
        $set = '';
        foreach ($userData as $k => $v) {
            $set .= '`' . $k . '`="' . $v . '", ';
        }
        $set = substr(trim($set), 0, -1);
        $sql = 'UPDATE `users` SET ' . $set . ' WHERE odnoklassniki=' . $this->user_info['uid'];
        return DB::get()->query($sql);
    }

    /**
     * Регистрация пользователя
     * 
     * @return boolean Результат выполнения операции
     */
    protected function registration() {
        $userData = $this->formUserInfoArray();

        if (UserModel::get()->add($userData)) {
            return true;
        }
        return false;
    }

    /**
     * Формирование массива информации, пригодного для добавления в БД
     * 
     * @return array $userData Массив с информацией
     */
    private function formUserInfoArray() {
        try {

            $tr = new Translit();
            $userData = [];
            // Name
            if (isset($this->user_info['first_name'])) {
                $userData['name'] = $this->user_info['first_name'];
                $userData['name_en'] = $tr->cyrToLat($this->user_info['first_name']);
            }
            // Surname
            if (isset($this->user_info['last_name'])) {
                $userData['surname'] = $this->user_info['last_name'];
                $userData['surname_en'] = $tr->cyrToLat($this->user_info['last_name']);
            }
            // ID
            if (isset($this->user_info['uid'])) {
                $userData['odnoklassniki'] = $this->user_info['uid'];
                // Profile url
                $userData['odnoklassniki_profile_url'] = 'http://ok.ru/profile/' . $this->user_info['uid'];
            }
            // Count /?
            if (isset($this->user_info['count'])) {
                $userData['odnoklassniki_count'] = $this->user_info['count'];
            }
            // Language
            $userData['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));
        } catch (Exception $e) {

            View::get()->error(404);
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }

        return $userData;
    }

    /**
     * Проверяет зарегистрирован ли пользователь
     * 
     * @return boolean true - зарегистрирован, false - не зарегистрирован
     */
    protected function isRegistered($userID) {
        if (DB::get()->select('SELECT `id` FROM `users` WHERE `odnoklassniki` =' . $userID)) {
            return true;
        }
        return false;
    }

    /**
     * Получение токена, используя CURL
     */
    private function getAccessToken() {
        $fields = [
            'code' => $this->code,
            'client_id' => self::APP_ID,
            'client_secret' => self::SECRET_KEY,
            'redirect_uri' => 'http://' . $GLOBALS['config']['domain'] . '/okauth',
            'grant_type' => 'authorization_code'
        ];
        $this->access_token = $this->curl->post_json(self::TOKEN_URL, $fields);
    }

    /**
     * Получение пользовательской информации через API
     */
    public function getUserInfo() {
        $fields = [
            'method' => 'users.getCurrentUser',
            'access_token' => $this->access_token['access_token'],
            'application_key' => self::PUBLIC_KEY,
            'format' => 'json',
            'sig' => $this->getSign('users.getCurrentUser', 'json')
        ];
        $this->user_info = $this->curl->get_json(self::USER_INFO_URL . '?' . urldecode(http_build_query($fields)));
        $this->user_info['count'] = count($this->getUserFriends());
    }

    /**
     * Получение "подписи", необходимой для авторизации
     * 
     * @return string Подпись
     */
    private function getSign($method = 'users.getCurrentUser', $format = '') {
        if (!empty($format)) {
            return md5("application_key=" . self::PUBLIC_KEY . "format=" . $format . "method=" . $method . md5("{$this->access_token['access_token']}" . self::SECRET_KEY));
        }
        return md5("application_key=" . self::PUBLIC_KEY . "method=" . $method . md5("{$this->access_token['access_token']}" . self::SECRET_KEY));
    }

    /**
     * Получение друзей пользователя
     * 
     * @return array Массив 
     */
    private function getUserFriends() {
        $sig = $this->getSign('friends.get');
        $fields = [
            'method' => 'friends.get',
            'application_key' => self::PUBLIC_KEY,
            'sig' => $sig,
            'access_token' => $this->access_token['access_token']
        ];
        return $this->curl->get_json(self::USER_INFO_URL, $fields);
    }

    /**
     * Получение ссылки для авторизации
     * 
     * @return string Ссылка
     */
    public static function getAuthLink() {
        return "http://odnoklassniki.ru/oauth/authorize?client_id=" . self::APP_ID . "&response_type=code&redirect_uri=http://" . $GLOBALS['config']['domain'] . "/okauth";
    }

}
