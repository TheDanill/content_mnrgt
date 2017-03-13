<?php

use GeoIp2\Database\Reader;

/**
 * Модель авторизации в Facebook
 */
class FbAuthModel extends SocialNetworkAuthModel {

    /**
     * @var integer Идентификатор приложения в FB.
     */
    const APP_ID = '116327792135624',
//    TEST
//    const APP_ID = '146702685764801',
            /**
             * @var string Секретный ключ приложения FB.
             */
            APP_SECRET_KEY = '43cfc529397fdeef0884abf6612194ca',
//            TEST
//            APP_SECRET_KEY = '4e3d611118647fd651c64efb8874efc0',
            /**
             * Данные аккаунта для парсинга страниц 
             */
            EMAIL = 'topstarru@ya.ru',
            PASSWORD = 'NE6sqK59AZktdA';

    /**
     * @var string Ключ доступа авторизации приложения.
     */
    public $code,
            /**
             * @var array Массив с данными о авторизации (access_token, expire, user_id).
             */
            $auth_info = [],
            /**
             *  @var array Массив с информацией о пользователе (заполняется с использованием API FB)
             */
            $user_info = [];

    /**
     * Конструктор авторизации через FB. 
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     *  Авторизация и внесение пользователя в БД.
     * 
     * @return boolean Результат выполнения
     */
    public function authorization() {

        try {
            parent::authorization();
            // Проверка на авторизацию
            if (isset($_SESSION['info'])) {
                $authorized_user_info = $_SESSION['info'];
            }

            if (!AuthModel::get()->isAuthorized()['access_token']) {
                parse_str($this->curl->get('https://graph.facebook.com/oauth/access_token?client_id=' . self::APP_ID . '&redirect_uri=http://' . $GLOBALS['config']['domain'] . '/fbauth&client_secret=' . self::APP_SECRET_KEY . '&code=' . $this->code . '', [], 'text/html', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', false, 0));
                $this->auth_info['access_token'] = $access_token;
                $this->auth_info['expires'] = $expires;
                $_SESSION['access_token'] = $access_token;
                $_SESSION['expires'] = $expires;
                $this->user_info = $this->curl->get_json('https://graph.facebook.com/me?access_token=' . $access_token . '', [], 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', false, 0);
            }
            // Проверяем, если пользователь уже добавлен
            if (isset($authorized_user_info['id'])) {
                // Пользователь залогинен
                // Проверка на наличие такого пользователя в системе
                $userDataFromFB = $this->getUserInfoFromFB();
                $this->updateUserInfoByID($authorized_user_info['id'], $userDataFromFB);
                
                // Подсчет рейтинга 
                if (isset(UserModel::get()->info['id']) && (!empty(UserModel::get()->info['id']))) {
                    (new RatingModel(UserModel::get()->info['id']))->recalcOneUser();
                }

                $user = UserModel::get()->getInfoBySocId($this->user_info['id'], 'facebook');
                // Обновить кэш
                Cache::redis()->delete('query_' . DB::OBJECT . '_' . md5(UserModel::get()->getSelectQuery($user['id'], 'active')));

                $user['access_token'] = $_SESSION['access_token'];
                $user['expires'] = $_SESSION['expires'];
                $_SESSION['info'] = $user;

                return $user['id'];
            } else {
                // Пользователь не залогинен. Стандартная регистрация
                // Проверка на наличие пользователя в системе
                if ($this->isRegistered($this->user_info['id'])) {
//                    $this->updateUserInfo();
                    $user = UserModel::get()->getInfoBySocId($this->user_info['id'], 'facebook');
                    $user['access_token'] = $_SESSION['access_token'];
                    $user['expires'] = $_SESSION['expires'];
                    $_SESSION['info'] = $user;
                    return $user['id'];
                } else {
                    // Регистрация пользователя
                    if ($this->registration()) {
                        $user = UserModel::get()->getInfoBySocId($this->user_info['facebook'], 'facebook');
                        
                        // Подсчет рейтинга
                        if (isset($user['id']) && (!empty($user['id']))) {
                            (new RatingModel($user['id']))->recalcOneUser();
                        }
                        
                        $user['token'] = $_SESSION['access_token'];
                        $user['expires'] = $_SESSION['expires'];
                        $_SESSION['info'] = $user;
                        // calculate rating
//                        $ratingModel = new RatingModel($user['id']);
//                        $ratingModel->updateRating();
                        return $user['id'];
                    } else {
                        throw new Exception('[FB] Регистрация пользователя не прошла.');
                    }
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     * Регистрация пользователя
     * 
     * @return int Идентификатор добавленного пользователя
     */
    protected function registration() {
        if ($this->user_info = $this->getUserInfoFromFB()) {
            return UserModel::get()->add($this->user_info);
        }
    }

    /**
     * Обновление информации о пользователе при очередном входе
     * 
     * @return mixed Результат выполнения запроса
     */
    private function updateUserInfo($data) {
        try {
            $userData = isset($data) ? $data : $this->getUserInfoFromFB();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= '`' . $k . '`="' . $v . '", ';
                }
            } else {
                throw new Exception('[FB] Пустые данные из функции getUserInfoFromFB в функции updateUserInfo');
            }
            $set = substr(trim($set), 0, -1);
            $sql = 'UPDATE `users` SET ' . $set . ' WHERE facebook= ' . DB::get()->escape($this->user_info['id'], true);
            return DB::get()->query($sql);
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     * Обновление информации о пользователе при очередном входе
     * 
     * @return mixed Результат выполнения запроса
     */
    private function updateUserInfoByID($id, $data) {
        try {
            $userData = isset($data) ? $data : $this->getUserInfoFromFB();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= "`" . $k . "`= IF(`" . $k . "` IN ('', '0000-00-00'), " . DB::get()->escape($v, true) . ", IFNULL(`" . $k . "`, " . DB::get()->escape($v, true) . ")), ";
                }
            } else {
                throw new Exception('[FB] Пустые данные из функции getUserInfoFromFB в функции updateUserInfoByID');
            }
            $set = substr(trim($set), 0, -1);
            $sql = 'UPDATE `users` SET ' . $set . ' WHERE id=' . DB::get()->escape($id) . '';
            return DB::get()->query($sql);
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     * Проверка зарегистрирован ли пользователь
     * 
     * @param int $userId Идентификатор пользователя в Facebook 
     * 
     * @return boolean Результат выполнения операции
     */
    protected function isRegistered($userId) {
        if (DB::get()->select('
            SELECT id FROM `users` WHERE `facebook` = ' . DB::get()->escape($userId) . '
            ', DB::ASSOC)) {
            return true;
        }
        return false;
    }

    /**
     * Получение информации о пользователе из FB (Имя, фамилия, страна).
     * 
     * @return mixed Массив с информацией, либо false в случае ошибки
     */
    private function getUserInfoFromFB($data = NULL) {

        $infoArr = [];

        if (isset($data)) {
            $obj = $data;
        } else {
            $obj = $this->curl->get_json('https://graph.facebook.com/' . $this->user_info['id'] . '?access_token=' . $this->auth_info['access_token'] . '&fields=first_name,last_name,link,friends,email,birthday,cover,gender,locale,location{location}', [], '', false, 0);
        }

        //$this->getUserFriends($obj['id']);
        $tr = new Translit();

        try {

            // ID
            if (isset($obj['id'])) {
                $infoArr['facebook'] = $obj['id'];
            } else {
                throw new Exception('[FB] Нет id пользователя');
            }

            // Имя
            if (isset($obj['last_name'])) {
                $infoArr['name'] = $obj['last_name'];
                $infoArr['name_en'] = $tr->cyrToLat($obj['last_name']);
            } else {
                throw new Exception('[FB] Нет имени пользователя');
            }

            // Фамилия
            if (isset($obj['first_name'])) {
                $infoArr['surname'] = $obj['first_name'];
                $infoArr['surname_en'] = $tr->cyrToLat($obj['first_name']);
            } else {
                throw new Exception('[FB] Нет фамилии пользователя');
            }

            // Ссылка 
            if (isset($obj['link'])) {
                $infoArr['facebook_profile_url'] = $obj['link'];
            } else {
                throw new Exception('[FB] Нет ссылки на страницу пользователя');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }

        // Ссылка 
        if (isset($obj['link'])) {
            $infoArr['facebook_profile_url'] = $obj['link'];
        }

        // Email
        if (isset($obj['email'])) {
            $infoArr['email'] = $obj['email'];
        }

        // Birthday
        try {
            if (isset($obj['birthday'])) {
                $d = DateTime::createFromFormat('d/m/Y', $obj['birthday']);
                if ($d) {
                    $infoArr['birthday'] = $d->format('Y-m-d');
                }
            }
        } catch (Exception $e) {
            unset($infoArr['birthday']);
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // friends
        try {
            if (isset($obj['friends']['summary']['total_count'])) {
                $infoArr['facebook_count'] = $obj['friends']['summary']['total_count'];
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // gender
        try {
            if (isset($obj['gender'])) {
                if ($obj['gender'] == 'female') {
                    $infoArr['gender'] = 'W';
                } else if ($obj['gender'] == 'male') {
                    $infoArr['gender'] = 'M';
                } else {
                    $infoArr['gender'] = 'A';
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // country
        try {
            // country
            if (isset($obj['location']['location']['country'])) {

                // Check english language
                if (strlen($obj['location']['location']['country']) == mb_strlen($obj['location']['location']['country'], 'utf-8')) {
                    $row_country_name = 'country_name_en';
                } else {
                    $row_country_name = 'country_name';
                }

                $result_country = DB::get()->select('SELECT `id`  FROM `countries` WHERE `' . $row_country_name . '`= ' . DB::get()->escape($obj['location']['location']['country'], true) . ';', DB::ASSOC);

                if (isset($result_country['response'][0]['id']) && is_int($result_country['response'][0]['id'])) {
                    $infoArr['country_id'] = $result_country['response'][0]['id'];
                } else {
                    $countryModel = new CountryModel();
                    $reader = new Reader(__DIR__ . '/../vendor/GeoLite2-Country.mmdb');
                    $record = $reader->country($_SERVER['REMOTE_ADDR']);
                    $id_country = $countryModel->getCountryIdByISO($record->country->isoCode);

                    if ($id_country) {
                        $infoArr['country_id'] = $id_country;
                    } else {
                        throw new Exception('[FB] ISO кода такой страны ' . $record->country->isoCode . " не существует.");
                    }
                }
            } else {

                $countryModel = new CountryModel();

                $reader = new Reader(__DIR__ . '/../vendor/GeoLite2-Country.mmdb');
                $record = $reader->country($_SERVER['REMOTE_ADDR']);
                $id_country = $countryModel->getCountryIdByISO($record->country->isoCode);
                if ($id_country) {
                    $infoArr['country_id'] = $id_country;
                } else {
                    throw new Exception('[FB] ISO кода такой страны ' . $record->country->isoCode . " не существует.");
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // city 
        try {

            if (isset($infoArr['country_id'])) {

                if (isset($obj['location']['location']['city'])) {

                    $result = DB::get()->select('SELECT `id`  FROM `citys` WHERE `name`= ' . DB::get()->escape($obj['location']['location']['city'], true) . ';', DB::ASSOC);

                    if (!$result) {
                        // Записываем данные о городе в базу данных
                        $data['country_id'] = $infoArr['country_id'];
                        $data['name'] = $obj['location']['location']['city'];
                        $id = DB::get()->insert('citys', $data);

                        $infoArr['city_id'] = $id;
                    } else {
                        $infoArr['city_id'] = $result[0]['id'];
                    }
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // Язык
        $infoArr['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));

        // picture
        try {
            $obj_image = $this->curl->get_json('https://graph.facebook.com/me/picture?access_token=' . $this->auth_info['access_token'] . '&redirect=0&height=200&width=200&type=normal', [], '', false, 0);

            if (isset($obj_image['data']['url'])) {
                $path_parts = pathinfo(substr($obj_image['data']['url'], 0, strpos($obj_image['data']['url'], "?")));
                $this->curl->load($obj_image['data']['url'], _DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension']);
                if (file_exists(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'])) {
                    $infoArr['avatar'] = $path_parts['filename'] . '.' . $path_parts['extension'];
                    Image::save(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'], 40, 40, _DIR_IMAGES . '40x40/' . $path_parts['filename'] . '.' . $path_parts['extension'], false);
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        if (!empty($infoArr)) {
            try {

                if (isset($this->auth_info['access_token'])) {
                    $infoArr['facebook_access_key'] = $this->auth_info['access_token'];
                } else {
                    throw new Exception('[FB] Access key is empty');
                }
            } catch (Exception $e) {
                ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            }
            return $infoArr;
        }

        return false;
    }

    /**
     * @todo Получение друзей путем парсинга 
     */
    public function getUserFriends($id) {
        $this->loginInFb();
        echo $this->curl->post('https://facebook.com/profile.php?id=' . $id, '', [], 'text/html', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3', false, 1, [CURLOPT_POST => 1, CURLOPT_HEADER => 0, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HTTPHEADER => ['Accept-Charset: utf-8', 'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3', 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'],
            CURLOPT_COOKIEFILE => getcwd() . '/cookies/cook.txt', CURLOPT_COOKIEJAR => getcwd() . '/cookies/cook.txt',
            CURLOPT_RETURNTRANSFER => 1, CURLOPT_REFERER => 'http://m.facebook.com']);
    }

    /**
     * @todo Логинится в FB Curl`ом. Пишет куки в файл
     */
    private function loginInFb() {
        $this->curl->post('https://m.facebook.com/login.php', 'charset_test=€,´,€,´,水,Д,Є&email=' . urlencode(self::EMAIL) . '&pass=' . urlencode(self::PASSWORD) . '&login=Login', [], 'text/html', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3', false, 1, [CURLOPT_POST => 1, CURLOPT_HEADER => 0, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HTTPHEADER => ['Accept-Charset: utf-8', 'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3', 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'],
            CURLOPT_COOKIEFILE => getcwd() . '/cookies/cook.txt', CURLOPT_COOKIEJAR => getcwd() . '/cookies/cook.txt',
            CURLOPT_RETURNTRANSFER => 1, CURLOPT_REFERER => 'http://m.facebook.com']);
    }

    public static function getAuthLink() {
        return "https://www.facebook.com/dialog/oauth?client_id=" . self::APP_ID . "&scope=email,user_birthday,user_location,user_friends&redirect_uri=http://" . $GLOBALS['config']['domain'] . "/fbauth&response_type=code";
    }

}
