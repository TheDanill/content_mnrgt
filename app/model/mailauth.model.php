<?php

/**
 * Модель авторизации через Mail.Ru
 */
class MailAuthModel extends SocialNetworkAuthModel {

    const APP_ID = 745859,
            PRIVATE_KEY = 'b407ae8c7881f105d56fc5cb57994476',
            SECRET_KEY = 'a0931895b1c626b8fdecf7941b5f02eb';

    /**
     * @var array $accessInfoArray Массив информации авторизации
     */
    private $accessInfoArray = [],
            /**
             * @var array $userInfo Массив пользовательской информации
             */
            $userInfo = [];

    /**
     * @var object $curl Объект для работы с HTTP запросами
     */
    protected $curl;

    /**
     * Конструктор объекта
     * 
     * @param string $code Response от сервера
     */
    public function __construct($code) {
        parent::__construct();
        $this->accessInfoArray = $this->curl->post_json('https://connect.mail.ru/oauth/token', [
            'client_id' => self::APP_ID,
            'client_secret' => self::SECRET_KEY,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'http://' . $GLOBALS['config']['domain'] . '/mailauth'
        ]);
        $_SESSION = $this->accessInfoArray;
    }

    /**
     * Авторизация пользователя или регистрация пользователя. 
     * 
     * @return boolean Результат выполнения операции
     */
    public function authorization() {
        parent::authorization();
        $_SESSION['name'] = $this->userInfo['first_name'];
        $_SESSION['surname'] = $this->userInfo['last_name'];
        $_SESSION['id'] = $this->userInfo['uid'];
        if ($this->isRegistered($this->userInfo['uid'])) {
            $user = UserModel::get()->getInfoBySocId($this->userInfo['uid'], 'mailru');
            $_SESSION['info'] = $user;
            if ($this->updateUserInfo())
                return true;
        }
        else {
            if ($this->registration()) {
                $user = UserModel::get()->getInfoBySocId($this->userInfo['uid'], 'mailru');
                $_SESSION['info'] = $user;
                return true;
            }
        }
        return false;
    }

    /**
     * Обновление информации о пользователе
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
        $sql = 'UPDATE `users` SET ' . $set . ' WHERE mailru=' . $this->userInfo['uid'];
        return DB::get()->query($sql);
    }

    /**
     * Регистрация пользователя в системе
     * 
     * @return boolean true или false в зависимости от результата
     */
    protected function registration() {
        if (UserModel::get()->add($this->formUserInfoArray())) {
            return true;
        }
        return false;
    }

    /**
     * Формирование массива, пригодного для добавления в БД
     *
     * @return array Результирующий массив с пользовательской информацией
     */
    private function formUserInfoArray() {
        try {
            $translate = new Translit();
            $userData = [];
            // Name
            if (isset($this->userInfo['first_name'])) {
                $userData['name'] = $this->userInfo['first_name'];
                $userData['name_en'] = $translate->cyrToLat($this->userInfo['first_name']);
            }
            // Surname
            if (isset($this->userInfo['last_name'])) {
                $userData['surname'] = $this->userInfo['last_name'];
                $userData['surname_en'] = $translate->cyrToLat($this->userInfo['last_name']);
            }
            // Count /?
            if (isset($this->userInfo['friends_count'])) {
                $userData['mailru_count'] = $this->userInfo['friends_count'];
            }
            // UID
            if (isset($this->userInfo['uid'])) {
                $userData['mailru'] = $this->userInfo['uid'];
            }
            // Profile url
            if (isset( $this->userInfo['link'] )) {
                $userData['mailru_profile_url'] = $this->userInfo['link'];
            }
            // Language
            $userData['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));
        } catch (Exception $e) {
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
        if (DB::get()->select('SELECT `id` FROM `users` WHERE `mailru` = ' . $userID)) {
            return true;
        }
        return false;
    }

    /**
     * Получение "подписи" для авторизации
     * 
     * @return string md5 хэш
     */
    private function getSign() {
        return md5("app_id=" . self::APP_ID . "method=users.getInfosecure=1session_key=" . $this->accessInfoArray['access_token'] . self::SECRET_KEY);
    }

    /**
     * Получение информации о пользователе через API Mail.Ru
     */
    public function getUserInfo() {
        $params = [
            'method' => 'users.getInfo',
            'secure' => '1',
            'app_id' => self::APP_ID,
            'session_key' => $this->accessInfoArray['access_token'],
            'sig' => $this->getSign()
        ];
        $userInfo = $this->curl->get_json('https://www.appsmail.ru/platform/api', $params);
        $this->userInfo = $userInfo[0];
    }

    /**
     * Приводит исходный массив стран к виду name => id
     * 
     * @return array Массив стран
     */
    private function getCountrysArr($countries) {
        $outArr = [];
        foreach ($countries as $c) {
            $outArr[$c['country_name']] = $c['id'];
        }
        return $outArr;
    }

    public static function getAuthLink() {
        return "https://connect.mail.ru/oauth/authorize?client_id=" . self::APP_ID . "&response_type=code&redirect_uri=http://" . $GLOBALS['config']['domain'] . "/mailauth";
    }

}
