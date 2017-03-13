<?php

use GeoIp2\Database\Reader;

/**
 * Модель авторизации через Вконтакте
 */
class VkAuthModel extends SocialNetworkAuthModel {

    /**
     * @var integer Идентификатор приложения в ВК.
     */
    const CLIENT_ID = 5545529,
//    TEST
//    const CLIENT_ID = 5562735,
            /*
             * @var string Секретный ключ приложения ВК.
             */
            CLIENT_SECRET_KEY = 'i6SNtvxaOSEbNUMrIToA';
//            TEST
//            CLIENT_SECRET_KEY = 'YrFi6sXm9JOCtBxRBDyX';

    /**
     * @var string Ключ доступа авторизации приложения.
     */
    public $code,
            /**
             * @var array Массив с данными о авторизации (access_token, expire, user_id).
             */
            $auth_info = [],
            /**
             *  @var array Массив с информацией о пользователе (заполняется с использованием API VK)
             */
            $user_info = [];

    /**
     * Конструктор авторизации через VK
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Авторизация и/или регистрация и внесение пользователя в БД.
     * @return boolean true - пользователь авторизован, false - не авторизован
     */
    public function authorization() {

        try {
            parent::authorization();
            
            // Проверка на авторизацию
            if (isset($_SESSION['info'])) {
                $authorized_user_info = $_SESSION['info'];
            }
            
            if (!AuthModel::get()->isAuthorized()['vk_access_token']) {
                $this->auth_info = $this->curl->get_json('https://oauth.vk.com/access_token?client_id=' . self::CLIENT_ID . '&client_secret=' . self::CLIENT_SECRET_KEY . '&redirect_uri=http://' . $GLOBALS['config']['domain'] . '/vkauth&code=' . $this->code, [], 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', false, 0);
            }

            if (isset($this->auth_info['user_id'])) {
                
                $_SESSION['vk_access_token'] = $this->auth_info['access_token'];
                $_SESSION['expires_in'] = $this->auth_info['expires_in'];
                $_SESSION['vk_id'] = $this->auth_info['user_id'];
                
                // Проверяем, если пользователь уже добавлен
                if (isset($authorized_user_info['id'])) {
                    // Пользователь залогинен
                    // Проверка на наличие такого пользователя в системе
                    $userDataFromVK = $this->getUserInfoFromVk();
                    
                    $this->updateUserInfoByID($authorized_user_info['id'], $userDataFromVK);
                    
                    // Подсчет рейтинга 
                    if (isset(UserModel::get()->info['id']) && (!empty(UserModel::get()->info['id']))) {
                        (new RatingModel(UserModel::get()->info['id']))->recalcOneUser();
                    }
                    
                    $user = UserModel::get()->getInfoBySocId($this->auth_info['user_id'], 'vkontakte');
                    
                    // Обновить кэш
                    Cache::redis()->delete('query_' . DB::OBJECT . '_' . md5(UserModel::get()->getSelectQuery($user['id'], 'active')));
                    
                    $user['access_token'] = $_SESSION['vk_access_token'];
                    $user['expires'] = $_SESSION['expires_in'];
                    $_SESSION['info'] = $user;

                    return $user['id'];
                } else {
                    // Пользователь не залогинен. Стандартная регистрация
                    // Проверка на наличие пользователя в системе
                    if ($this->isRegistered($this->auth_info['user_id'])) {
                        $user = UserModel::get()->getInfoBySocId($this->auth_info['user_id'], 'vkontakte');
                        $_SESSION['info'] = $user;
                        return $user['id'];
                    } else {
                        // Регистрация пользователя
                        if ($this->registration()) {
                            
                            $user = UserModel::get()->getInfoBySocId($this->auth_info['user_id'], 'vkontakte');
                            
                            // Подсчет рейтинга
                            if (isset($user['id']) && (!empty($user['id']))) {
                                (new RatingModel($user['id']))->recalcOneUser();
                            }
                            
                            $_SESSION['info'] = $user;
                            // calculate rating
//                            $ratingModel = new RatingModel($user['id']);
//                            $ratingModel->updateRating();
                            return $user['id'];
                        } else {
                            throw new Exception('[VK] Регистрация пользователя не прошла.');
                        }
                    }
                    
                }
                
            } else {

                throw new Exception('[VK] Error vk. Empty user id');
                return false;
                
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    /**
     * Обновление информации о пользователе при очередном входе
     * @return mixed Результат запроса
     */
    private function updateUserInfo($data) {
        try {
            $userData = isset($data) ? $data : $this->getUserInfoFromVk();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= '`' . DB::get()->escape( $k ) . '`= ' . DB::get()->escape( $v, true ) . ' , ';
                }
                $set = substr(trim($set), 0, -1);
                $sql = 'UPDATE `users` SET ' . $set . ' WHERE vkontakte=' . DB::get()->escape( $userData['vkontakte'], true );
                return DB::get()->query($sql);
            } else {
                throw new Exception('[VK] Пустые данные из функции getUserInfoFromVk в функции updateUserInfo');
            }
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
            $userData = isset($data) ? $data : $this->getUserInfoFromVk();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= "`" . $k . "`= IF(`" . $k . "` IN ('', '0000-00-00'), " . DB::get()->escape($v, true) . ", IFNULL(`" . $k . "`, " . DB::get()->escape($v, true) . ")), ";
                }
                $set = substr(trim($set), 0, -1);
                $sql = 'UPDATE `users` SET ' . $set . ' WHERE id=' . DB::get()->escape( $id ) . '';
                return DB::get()->query($sql);
            } else {
                throw new Exception('[VK] Пустые данные из функции getUserInfoFromVk в функции updateUserInfoByID');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     *  Регистрация пользователя
     *  @return mixed Результат запроса
     */
    protected function registration() {
        if ($this->user_info = $this->getUserInfoFromVk()) {
            return UserModel::get()->add($this->user_info);
        }
    }

    /**
     * Проверка зарегистрирован ли пользователь
     * @return boolean Результат запроса
     */
    protected function isRegistered($userId) {
        if (DB::get()->select('SELECT id FROM `users` WHERE `vkontakte` = "' . $userId . '"
            ', DB::ASSOC)) {
            return true;
        }
        return false;
    }

    /**
     * Получение информации о пользователе из VK (Имя, фамилия, страна).
     * @return array|false Массив с пользовательской информацией
     */
    private function getUserInfoFromVk() {

        $infoArr = [];

        $obj = $this->curl->get_json('https://api.vk.com/method/users.get?user_id=' . $this->auth_info['user_id'] . '&fields=first_name,last_name,bdate,sex,deactivated,country,city,domain,nickname,photo_200&lang=ru', [], '', false, 0);
        
        
        $translit = new Translit();
        
        // BASE FIELDS

        try {

            if (isset($obj['response'][0]['uid'])) {
                $infoArr['vkontakte'] = $obj['response'][0]['uid'];
            } else {
                throw new Exception( '[VK] Нет id пользователя' );
            }

            if (isset($obj['response'][0]['first_name'])) {
                $infoArr['name'] = $obj['response'][0]['first_name'];
                $infoArr['name_en'] = $translit->cyrToLat( $obj['response'][0]['first_name'] );
            } else {
                throw new Exception( '[VK] Нет имени пользователя' );
            }

            if (isset($obj['response'][0]['last_name'])) {
                $infoArr['surname'] = $obj['response'][0]['last_name'];
                $infoArr['surname_en'] = $translit->cyrToLat( $obj['response'][0]['last_name'] );
            } else {
                throw new Exception( '[VK] Нет фамилии пользователя' );
            }

            if (isset($obj['response'][0]['deactivated'])) {
                // Страница удалена или забанена
                if ($obj['response'][0]['deactivated'] == 'deleted') {
                    // Пытается авторизоваться удаленный пользователь 
                } else if ($obj['response'][0]['deactivated'] == 'banned') {
                    // Пользователь удален
                }
            }

            if (isset($obj['response'][0]['hidden'])) {
                // Пользователь скрыл свой профиль
            }
            
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }

        
        // ADDITIONAL FIELDS
        // Country
        
        try {

            if (isset($obj['response'][0]['country'])) {

                // Узнать страну из данных пользователя

                $countryModel = new CountryModel();

                if ($countryModel->issetCountry($obj['response'][0]['country'])) {

                    $infoArr['country_id'] = $obj['response'][0]['country'];
                    
                } else {

                    if (isset($_SERVER['REMOTE_ADDR'])) {

                        $countryModel = new CountryModel();

                        $reader = new Reader(__DIR__ . '/../vendor/GeoLite2-Country.mmdb');
                        $record = $reader->country($_SERVER['REMOTE_ADDR']);
                        $id_country = $countryModel->getCountryIdByISO($record->country->isoCode);
                        if ($id_country) {
                            $infoArr['country_id'] = $id_country;
                        } else {
                            throw new Exception('[VK] ISO кода такой страны ' . $record->country->isoCode . " не существует.");
                        }
                    } else {

                        throw new Exception('[VK] IP пользователя не указан');
                    }

                    throw new Exception('[VK] Нет данных такой страны' . $obj['response'][0]['country']);
                }
                
            } else {

                // Узнать страну по IP адресу

                if (isset($_SERVER['REMOTE_ADDR'])) {

                    $countryModel = new CountryModel();

                    $reader = new Reader(__DIR__ . '/../vendor/GeoLite2-Country.mmdb');
                    $record = $reader->country($_SERVER['REMOTE_ADDR']);
                    $id_country = $countryModel->getCountryIdByISO($record->country->isoCode);
                    if ($id_country) {
                        $infoArr['country_id'] = $id_country;
                    } else {
                        throw new Exception('[VK] ISO кода такой страны ' . $record->country->isoCode . " не существует.");
                    }
                } else {

                    throw new Exception('[VK] IP пользователя не указан');
                    
                }
                
            }
            
        } catch (Exception $e) {

            $infoArr['country_id'] = null;

            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        // city

        try {

            if (isset($obj['response'][0]['city'])) {
                
                $city = $this->curl->get_json('https://api.vk.com/method/database.getCitiesById?city_ids=' . $obj['response'][0]['city'] . '&lang=ru', [], '', false, 0);
                
                if ( isset($city['response'][0]['name']) ) {

                    $result = DB::get()->select('SELECT `id`  FROM `citys` WHERE `name`= ' . DB::get()->escape( $city['response'][0]['name'], true ) . ';', DB::ASSOC);

                    if (!$result) {

                        // Записываем данные о городе в базу данных
                        $data['country_id'] = $infoArr['country_id'];
                        $data['name'] = $city['response'][0]['name'];
                        $id = DB::get()->insert( 'citys', $data );
                        
                        $infoArr['city_id'] = $id; 
                        
                    } else {
                        
                        $infoArr['city_id'] = $result[0]['id'];
                        
                    }
                } else {

                    throw new Exception("[VK] Нет такого города в базе Вконтакте");
                }
            } else {

                throw new Exception("[VK] Нет данных такого города");
            }
            
        } catch (Exception $e) {

            $infoArr['city_id'] = null;

            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            
        }
        
        // Адрес страницы
        if (isset($obj['response'][0]['domain'])) {
            $infoArr['vkontakte_profile_url'] = 'https://vk.com/' . $obj['response'][0]['domain'];
        }
        
        // Nickname
//        if (isset($obj['response'][0]['nickname'])) {
//            $infoArr['nickname'] = $obj['response'][0]['nickname'];
//        }
        
        // Язык
        $infoArr['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));
        
        // Аватар
        if (isset($obj['response'][0]['photo_200'])) {
            $path_parts = pathinfo($obj['response'][0]['photo_200']);
            $this->curl->load($obj['response'][0]['photo_200'], _DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension']);
            if (file_exists(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'])) {
                $infoArr['avatar'] = $path_parts['filename'] . '.' . $path_parts['extension'];
                Image::save(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'], 40, 40, _DIR_IMAGES . '40x40/' . $path_parts['filename'] . '.' . $path_parts['extension'], false);
            }
        }
        
        // День рождения
        try {
            if ( isset( $obj['response'][0]['bdate'] ) ) {
                
                $d = DateTime::createFromFormat( 'd.m.Y', $obj['response'][0]['bdate'] );
                if ($d) {
                    $infoArr['birthday'] = $d->format( 'Y-m-d' );
                }
                
            }
        } catch ( Exception $e ) {
            unset( $infoArr['birthday'] );
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        } 
        
        // Пол
        try {
            if ( isset($obj['response'][0]['sex']) ) {
                if ( $obj['response'][0]['sex'] == 1 ) {
                    $infoArr['gender'] = 'W';
                } else if ( $obj['response'][0]['sex'] == 2 ) {
                    $infoArr['gender'] = 'M';
                } else {
                    $infoArr['gender'] = 'A';
                }
            }
        } catch (Exception $e) {
            $infoArr['gender'] = 'A';
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
        
        // Количество друзей
        $obj = $this->curl->get_json('https://api.vk.com/method/friends.get?user_id=' . $this->auth_info['user_id']);
        if (!empty($obj['response'])) {
            $infoArr['vkontakte_friends_count'] = count($obj['response']);
        } else {
            $infoArr['vkontakte_friends_count'] = 0;
        }
        
        // Количество подписчиков
        $obj = $this->curl->get_json('https://api.vk.com/method/users.getFollowers?user_id=' . $this->auth_info['user_id']);
        if (isset($obj['response']['count'])) {
            $infoArr['vkontakte_followers_count'] = $obj['response']['count'];
        } else {
            $infoArr['vkontakte_followers_count'] = 0;
        }
        
        if (!empty($infoArr)) {

            try {

                if (isset($this->auth_info['access_token'])) {
                    $infoArr['vkontakte_access_key'] = $this->auth_info['access_token'];
                } else {
                    throw new Exception('[VK] Access key is empty');
                }

                if (isset($this->auth_info['expires_in'])) {
                    $infoArr['vkontakte_expires_in'] = $this->auth_info['expires_in'];
                } else {
                    throw new Exception('[VK] `expires in` field is empty');
                }
            } catch (Exception $e) {
                ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            }
            
            return $infoArr;
        }

        return false;
    }

    public static function getAuthLink() {
        return "https://oauth.vk.com/authorize?client_id=" . self::CLIENT_ID . "&redirect_uri=http://" . $GLOBALS['config']['domain'] . "/vkauth&display=popup";
    }

}
