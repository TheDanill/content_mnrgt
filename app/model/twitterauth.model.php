<?php

/**
 * Модель авторизации через Twitter
 */
class TwitterAuthModel extends SocialNetworkAuthModel {

    const CONS_KEY = 'YthOFTxj31ma57AVxmIyzfALS',
            CONS_SECRET = 'OXa00e45rkHRxiX6m4M9BsMsG1aGNVQrd0evXq31YYCleEfkpB';

    /*
     * @var string Уникальный хэш токен
     */

    private $oauth_nonce,
            /*
             * @var string Временная метка
             */
            $oauth_timestamp,
            /*
             * @var string подпись из параметров
             */
            $oauth_signature,
            /*
             * @var array Массив с пользовательской информацией
             */
            $userInfo;

    /**
     * @var array Массив с информацией об авторизации
     */
    public $authInfo = [];

    /**
     * Конструктор объекта
     * 
     * @param string $oauth_token Токен
     * @param string $oauth_verifier 
     * @param string $oauth_token_secret
     * @return boolean false - в случае неудачи
     */
    public function __construct($oauth_token = '', $oauth_verifier = '', $oauth_token_secret) {
        $this->setNonce();
        $this->setTimestamp();
        parent::__construct();
        if (empty($oauth_token) && empty($oauth_verifier) && empty($oauth_token_secret)) {
            $this->oauth_signature = $this->createSignature();
        } else {
            $this->oauth_signature = $this->createAccessSignature($oauth_token, $oauth_verifier, $oauth_token_secret);
        }
        return false;
    }

    /**
     * Авторизация и/или регистрация пользователя
     * 
     * @return boolean true - в случае успешной авторизации, false - в случае неудачи
     */
    public function authorization() {
        parent::authorization();

        // Проверка на авторизацию
        if (isset($_SESSION['info'])) {
            $authorized_user_info = $_SESSION['info'];
        }

        $_SESSION['info']['twitter_id'] = $this->authInfo['user_id'];
        $_SESSION['info']['screen_name'] = $this->authInfo['screen_name'];

        if (isset($this->authInfo['user_id'])) {

            // Проверяем, если пользователь уже добавлен
            if (isset($authorized_user_info['id'])) {

                $userDataFromTwitter = $this->formUserDataArray();
                $this->updateUserInfoByID($authorized_user_info['id'], $userDataFromTwitter);
                
                // Подсчет рейтинга 
                if (isset(UserModel::get()->info['id']) && (!empty(UserModel::get()->info['id']))) {
                    (new RatingModel(UserModel::get()->info['id']))->recalcOneUser();
                }
                
                $user = UserModel::get()->getInfoBySocId($this->authInfo['user_id'], 'twitter');

                // Обновить кэш
                Cache::redis()->delete('query_' . DB::OBJECT . '_' . md5(UserModel::get()->getSelectQuery($user['id'], 'active')));

                return $user['id'];
            } else {
                // Пользователь не залогинен. Стандартная регистрация
                // Проверка на наличие пользователя в системе

                if ($this->isRegistered($this->authInfo['user_id'])) {
                    $this->updateUserInfo();
                    $user = UserModel::get()->getInfoBySocId($this->authInfo['user_id'], 'twitter');
                    $_SESSION['info'] = $user;
                    return $user['id'];
                } else {
                    // Регистрация пользователя
                    if ($info = $this->registration()) {
                        $user = UserModel::get()->getInfoBySocId($this->authInfo['user_id'], 'twitter');
                        $_SESSION['info'] = $user;
                        
                        // Подсчет рейтинга
                        if (isset($user['id']) && (!empty($user['id']))) {
                            (new RatingModel($user['id']))->recalcOneUser();
                        }
                        
                        // calculate rating При включении возникает ошибка с запросом
//                        $ratingModel = new RatingModel($user['id']);
//                        $ratingModel->updateRating();
                        return $user['id'];
                    }
                    return false;
                }
            }
        }
    }

    /**
     * Регистрация пользователя
     * 
     * @return mixed Массив с данными пользователя, либо false - в случае неудачи
     */
    protected function registration() {
        $userData = $this->formUserDataArray();
        if (UserModel::get()->add($userData)) {
            return $userData;
        }
        return false;
    }

    /**
     * Обновление пользовательской информации
     * 
     * @return mixed Результат запроса
     */
    private function updateUserInfo($data = NULL) {
        try {
            $userData = isset($data) ? $data : $this->formUserDataArray();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= '`' . DB::get()->escape($k) . '`= ' . DB::get()->escape($v, true) . ' , ';
                }
                $set = substr(trim($set), 0, -1);
                $sql = 'UPDATE `users` SET ' . $set . ' WHERE `twitter`=' . DB::get()->escape($userData['twitter'], true);
                $result = DB::get()->query($sql);
                if (DB::get()->affected() !== 1) {
                    throw new Exception('[TWITTER] Пустые данные из функции formUserDataArray в функции updateUserInfo');
                }
                return $result;
            } else {
                return false;
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    private function updateUserInfoByID($id, $data = NULL) {
        try {
            $userData = isset($data) ? $data : $this->formUserDataArray();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= "`" . $k . "`= IF(`" . $k . "` IN ('', '0000-00-00'), " . DB::get()->escape($v, true) . ", IFNULL(`" . $k . "`, " . DB::get()->escape($v, true) . ")), ";
                }
                $set = substr(trim($set), 0, -1);
                $sql = 'UPDATE `users` SET ' . $set . ' WHERE id=' . DB::get()->escape($id) . '';
                return DB::get()->query($sql);
            } else {
                throw new Exception('[TWITTER] Пустые данные из функции getUserInfoFromFB в функции updateUserInfoByID');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     * Формирует массив пользовательской информации, пригодный для добавления/обновления в БД
     * 
     * @return array Массив с пользовательской информацией
     */
    private function formUserDataArray() {

        try {
            // Получение информации
            $this->getUserData();

            $tr = new Translit();
            $nameArr = $tr->splitNameSurname($this->userInfo['name']);
            $userData = [];
            // Name
            if (isset($nameArr[0])) {
                $userData['name'] = $nameArr[0];
                $userData['name_en'] = $tr->cyrToLat($nameArr[0]);
            } else {
                throw new Exception("[TWITTER] Нет имени пользователя");
            }
            // Surname
            if (isset($nameArr[1])) {
                $userData['surname'] = $nameArr[1];
                $userData['surname_en'] = $tr->cyrToLat($nameArr[1]);
            } else {
//                throw new Exception("[TWITTER] Нет фамилии пользователя");
            }
            // Screen name
            if (isset($this->userInfo['screen_name'])) {
                // Create profile url
                $userData['twitter_profile_url'] = 'https://twitter.com/' . $this->userInfo['screen_name'];
                // Nickname
//                $userData['nickname'] = $this->userInfo['screen_name'];
            }
            // ID 
            if (isset($this->userInfo['id_str'])) {
                $userData['twitter'] = $this->userInfo['id_str'];
            }
            // Followers count
            if (isset($this->userInfo['followers_count'])) {
                $userData['twitter_followers_count'] = $this->userInfo['followers_count'];
            }
            // Friends count
            if (isset($this->userInfo['friends_count'])) {
                $userData['twitter_friends_count'] = $this->userInfo['friends_count'];
            }
            // Image 
            if (isset($this->userInfo['profile_image_url'])) {
                $this->userInfo['profile_image_url'] = str_replace('_normal', '', $this->userInfo['profile_image_url']);
                $path_parts = pathinfo($this->userInfo['profile_image_url']);
                $this->curl->load($this->userInfo['profile_image_url'], _DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension']);
                if (file_exists(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'])) {
                    $userData['avatar'] = $path_parts['filename'] . '.' . $path_parts['extension'];
                    Image::save(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'], 40, 40, _DIR_IMAGES . '40x40/' . $path_parts['filename'] . '.' . $path_parts['extension'], false);
                }
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
     * Получение пользовательской информации с помощью API Twitter
     */
    private function getUserData() {
        $this->setNonce();
        $this->setTimestamp();
        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode('https://api.twitter.com/1.1/users/show.json') . '&';
        $oauth_base_text .= urlencode('oauth_consumer_key=' . self::CONS_KEY . '&');
        $oauth_base_text .= urlencode('oauth_nonce=' . $this->oauth_nonce . '&');
        $oauth_base_text .= urlencode('oauth_signature_method=HMAC-SHA1&');
        $oauth_base_text .= urlencode('oauth_timestamp=' . $this->oauth_timestamp . "&");
        $oauth_base_text .= urlencode('oauth_token=' . $this->authInfo['oauth_token'] . "&");
        $oauth_base_text .= urlencode('oauth_version=1.0&');
        $oauth_base_text .= urlencode('screen_name=' . $this->authInfo['screen_name']);
        $key = self::CONS_SECRET . '&' . $this->authInfo['oauth_token_secret'];
        $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
        $url = 'https://api.twitter.com/1.1/users/show.json';
        $url .= '?oauth_consumer_key=' . self::CONS_KEY;
        $url .= '&oauth_nonce=' . $this->oauth_nonce;
        $url .= '&oauth_signature=' . urlencode($signature);
        $url .= '&oauth_signature_method=HMAC-SHA1';
        $url .= '&oauth_timestamp=' . $this->oauth_timestamp;
        $url .= '&oauth_token=' . urlencode($this->authInfo['oauth_token']);
        $url .= '&oauth_version=1.0';
        $url .= '&screen_name=' . $this->authInfo['screen_name'];
        $this->userInfo = $this->curl->get_json($url);
    }

    /**
     * Проверяет зарегистрирован ли пользователь
     * 
     * @return boolean true - зарегистрирован, false - не зарегистрирован
     */
    protected function isRegistered($userID) {
        if (DB::get()->select('
            SELECT id, name, surname FROM `users` WHERE `twitter` = "' . $userID . '"
            ', DB::ASSOC)) {
            return true;
        }
        return false;
    }

    /**
     * Получение параметров для авторизации
     * 
     * @param boolean $access Нужны параметры для авторизации, либо для работы с API
     * @param string $oauth_token 
     * @param stirng $oauth_verifier
     */
    public function getAuthInfo($access = false, $oauth_token = '', $oauth_verifier = '') {
        if ($access) {
            parse_str($this->curl->get($this->createAccessUrl($oauth_token, $oauth_verifier)), $this->authInfo);
        } else {
            parse_str($this->curl->get($this->createAuthUrl($access)), $this->authInfo);
        }
        if (isset($this->authInfo)) {
            return true;
        }
        return false;
    }

    /**
     * Создание подписи для получения токена
     * 
     * @return string Строка подписи
     */
    private function createSignature() {
        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode("https://api.twitter.com/oauth/request_token") . "&";
        $oauth_base_text .= urlencode("oauth_callback=" . urlencode("http://" . $GLOBALS['config']['domain'] . "/twitterauth") . "&");
        $oauth_base_text .= urlencode("oauth_consumer_key=" . self::CONS_KEY . "&");
        $oauth_base_text .= urlencode("oauth_nonce=" . $this->oauth_nonce . "&");
        $oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
        $oauth_base_text .= urlencode("oauth_timestamp=" . $this->oauth_timestamp . "&");
        $oauth_base_text .= urlencode("oauth_version=1.0");
        $key = self::CONS_SECRET . '&';
        return base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
    }

    /**
     * Создание подписи для авторизации
     * 
     * @return string Строка подписи
     */
    private function createAccessSignature($oauth_token, $oauth_verifier, $oauth_token_secret) {
        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode("https://api.twitter.com/oauth/access_token") . "&";
        $oauth_base_text .= urlencode("oauth_consumer_key=" . self::CONS_KEY . "&");
        $oauth_base_text .= urlencode("oauth_nonce=" . $this->oauth_nonce . "&");
        $oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
        $oauth_base_text .= urlencode("oauth_token=" . $oauth_token . "&");
        $oauth_base_text .= urlencode("oauth_timestamp=" . $this->oauth_timestamp . "&");
        $oauth_base_text .= urlencode("oauth_verifier=" . $oauth_verifier . "&");
        $oauth_base_text .= urlencode("oauth_version=1.0");
        $key = self::CONS_SECRET . "&" . $oauth_token_secret;
        return base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
    }

    /**
     * Склейка URL для получения токена
     * 
     * @return string URL
     */
    private function createAuthUrl() {
        $url = 'https://api.twitter.com/oauth/request_token';
        $url .='?oauth_callback=' . urlencode("http://" . $GLOBALS['config']['domain'] . "/twitterauth");
        $url .='&oauth_consumer_key=' . self::CONS_KEY;
        $url .='&oauth_nonce=' . $this->oauth_nonce;
        $url .='&oauth_signature=' . urlencode($this->oauth_signature);
        $url .='&oauth_signature_method=HMAC-SHA1';
        $url .='&oauth_timestamp=' . $this->oauth_timestamp;
        $url .='&oauth_version=1.0';
        return $url;
    }

    /**
     * Склейка URL для аутентификации
     * 
     * @return string URL
     */
    private function createAccessUrl($oauth_token, $oauth_verifier) {
        $url = "https://api.twitter.com/oauth/access_token";
        $url .= '?oauth_nonce=' . $this->oauth_nonce;
        $url .= '&oauth_signature_method=HMAC-SHA1';
        $url .= '&oauth_timestamp=' . $this->oauth_timestamp;
        $url .= '&oauth_consumer_key=' . self::CONS_KEY;
        $url .= '&oauth_token=' . urlencode($oauth_token);
        $url .= '&oauth_verifier=' . urlencode($oauth_verifier);
        $url .= '&oauth_signature=' . urlencode($this->oauth_signature);
        $url .= '&oauth_version=1.0';
        return $url;
    }

    /**
     * Установить уникальный хэш токен
     */
    private function setNonce() {
        $this->oauth_nonce = md5(uniqid(rand(), true));
    }

    /**
     * Установить временную метку
     */
    private function setTimestamp() {
        $this->oauth_timestamp = time();
    }

    /**
     * Получить ссылку для авторизации
     * 
     * @return string URL 
     */
    public static function getAuthLink() {
        return "/twitterauth";
    }

}
