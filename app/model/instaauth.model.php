<?php

/**
 * Модель авторизации через Instagram
 */
class InstaAuthModel extends SocialNetworkAuthModel {

    /**
     * @var integer Идентификатор приложения в Instagram.
     */
    const CLIENT_ID = '17a52a675bc7498195604fe78f9877f5',
//    TEST
//    const CLIENT_ID = 'f4aa702b8ee743e8a2676383ea9e2581',
            /**
             * @var string Секретный ключ приложения Instagram.
             */
            CLIENT_SECRET_KEY = '1ee0043d8a514ba6bc18dc7ffba9d7cf';
//          TEST
//          CLIENT_SECRET_KEY = 'da794f428edf4993a0986793ff48b609';

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
     * Конструктор авторизации через Instagram. 
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Обновление информации о пользователе при очередном входе
     * 
     * @return mixed Результат выполнения запроса
     */
    private function updateUserInfo($data) {
        try {
            $userData = isset($data) ? $data : $this->getUserInfoFromInsta();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= '`' . $k . '`="' . $v . '", ';
                }
            } else {
                throw new Exception('[INSTAGRAM] Пришли пустые данные из функции getUserInfoFromInsta в функции updateUserInfo');
            }
            $set = substr(trim($set), 0, -1);
            $sql = 'UPDATE `users` SET ' . $set . ' WHERE instagram= ' . DB::get()->escape($this->user_info['id'], true);
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
            $userData = isset($data) ? $data : $this->getUserInfoFromInsta();
            $set = '';
            if (is_array($userData) && !empty($userData)) {
                foreach ($userData as $k => $v) {
                    $set .= "`" . $k . "`= IF(`" . $k . "` IN ('', '0000-00-00'), " . DB::get()->escape($v, true) . ", IFNULL(`" . $k . "`, " . DB::get()->escape($v, true) . ")), ";
                }
            } else {
                throw new Exception('[INSTAGRAM] Пришли пустые данные из функции getUserInfoFromInsta в функции updateUserInfoByID');
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
     *  Авторизация и внесение пользователя в БД.
     * 
     * @return boolean Результат выполнения операции
     */
    public function authorization() {
        // Проверка на авторизацию
        if (isset($_SESSION['info'])) {
            $authorized_user_info = $_SESSION['info'];
        }

        if (!AuthModel::get()->isAuthorized()['access_token']) {
            $this->auth_info = $this->curl->post_json('https://api.instagram.com/oauth/access_token', ['client_id' => self::CLIENT_ID, 'client_secret' => self::CLIENT_SECRET_KEY, 'grant_type' => 'authorization_code', 'redirect_uri' => 'http://' . $GLOBALS['config']['domain'] . '/instaauth', 'code' => $this->code]);
            $_SESSION['access_token'] = $this->auth_info['access_token'];
        }

        parent::authorization();

        if (isset($_SESSION['access_token'])) {

            // Проверяем, если пользователь уже добавлен
            if (isset($authorized_user_info['id'])) {

                // Пользователь залогинен
                // Проверка на наличие такого пользователя в системе
                $userDataFromInsta = $this->getUserInfoFromInsta();
                $this->updateUserInfoByID($authorized_user_info['id'], $userDataFromInsta);
                
                // Подсчет рейтинга
                if (isset(UserModel::get()->info['id']) && (!empty(UserModel::get()->info['id']))) {
                    (new RatingModel(UserModel::get()->info['id']))->recalcOneUser();
                }
                
                $user = UserModel::get()->getInfoBySocId($this->auth_info['user']['id'], 'instagram');
                // Обновить кэш
                Cache::redis()->delete('query_' . DB::OBJECT . '_' . md5(UserModel::get()->getSelectQuery($user['id'], 'active')));

                $user['access_token'] = $_SESSION['access_token'];
                $user['expires'] = $_SESSION['expires'];
                $_SESSION['info'] = $user;

                return $user['id'];
            } else {

                if ($this->isRegistered($this->auth_info['user']['id'])) {

                    $user = UserModel::get()->getInfoBySocId($this->auth_info['user']['id'], 'instagram');

                    $_SESSION['info'] = $user;
                    if (!isset($_SESSION['access_token'])) {
                        $_SESSION['access_token'] = $this->auth_info['access_token'];
                    }

                    return true;
                } else {

                    if ($this->registration()) {

                        $user = UserModel::get()->getInfoBySocId($this->auth_info['user']['id'], 'instagram');
                        // Подсчет рейтинга
                        if (isset($user['id']) && (!empty($user['id']))) {
                            (new RatingModel($user['id']))->recalcOneUser();
                        }
                        
                        $_SESSION['info'] = $user;
                        if (!isset($_SESSION['access_token'])) {
                            $_SESSION['access_token'] = $this->auth_info['access_token'];
                        }
                        
                    }
                }
            }
        } else {
            throw new Exception('[INSTAGRAM] Регистрация пользователя не прошла.');
        }

        return false;
    }

    /**
     * Регистрация пользователя
     * 
     * @return int Идентфикатор добавленного пользователя
     */
    protected function registration() {
        if ($this->user_info = $this->getUserInfoFromInsta()) {
            return UserModel::get()->add($this->user_info);
        }
    }

    /**
     * Проверка зарегистрирован ли пользователь
     * 
     * @return boolean Результат выполнения операции
     */
    protected function isRegistered($userId) {
        if (DB::get()->select('
            SELECT id FROM `users` WHERE `instagram` = "' . $userId . '"
            ', DB::ASSOC)) {
            return true;
        }
        return false;
    }

    /**
     * Получение информации о пользователе из Instagram (Имя, фамилия, страна)
     * 
     * @return mixed Массив с информацией или false в случае неудачи
     */
    private function getUserInfoFromInsta() {
        $infoArr = [];

        // Base data
        try {
            if (isset($this->auth_info)) {
                // Name and surname
                if (isset($this->auth_info['user']['full_name']) && ($this->auth_info['user']['full_name'] != "")) {
                    $nameArr = explode(' ', $this->auth_info['user']['full_name']);
                    $translate = new Translit();
                    $infoArr['name'] = $nameArr[0];
                    $infoArr['surname'] = $nameArr[1];
                    $infoArr['name_en'] = $translate->cyrToLat($nameArr[0]);
                    $infoArr['surname_en'] = $translate->cyrToLat($nameArr[1]);
                } else {
                    if (isset($this->auth_info['user']['username'])) {
                        $infoArr['nickname'] = $this->auth_info['user']['username'];
                    } else {
                        throw new Exception("[INSTAGRAM] Нет имени и ника пользователя");
                    }
                }

                // Image from profile
                if (isset($this->auth_info['user']['profile_picture'])) {
                    $path_parts = pathinfo($this->auth_info['user']['profile_picture']);
                    $this->curl->load($this->auth_info['user']['profile_picture'], _DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension']);
                    if (file_exists(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'])) {
                        $infoArr['avatar'] = $path_parts['filename'] . '.' . $path_parts['extension'];
                        Image::save(_DIR_IMAGES . $path_parts['filename'] . '.' . $path_parts['extension'], 40, 40, _DIR_IMAGES . '40x40/' . $path_parts['filename'] . '.' . $path_parts['extension'], false);
                    }
                }

                // Username aka nickname
//                if (isset($this->auth_info['user']['username'])) {
//                    $infoArr['nickname'] = $this->auth_info['user']['username'];
//                }

                // id 
                if (isset($this->auth_info['user']['id'])) {
                    $infoArr['instagram'] = $this->auth_info['user']['id'];
                } else {
                    throw new Exception("[INSTAGRAM] Нет id пользователя");
                }

                // language
                $infoArr['default_lang'] = Lang::get()->getIdByLang(array_search(Lang::get()->lang, Lang::get()->langs));
                
            } else {

                throw new Exception("[INSTAGRAM] Массив info auth пуст");
            }
        } catch (Exception $e) {
            View::get()->error(404);
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }

        // More data

        try {

            $obj = $this->curl->get_json('https://api.instagram.com/v1/users/' . $this->auth_info['user']['id'] . '/?access_token=' . $this->auth_info['access_token'] . '', [], '', false, 0);

            if (isset($obj['data']) && $obj['meta']['code'] == 200) {

                if (isset($obj['data']['counts']['followed_by'])) {
                    $infoArr['instagram_count'] = $obj['data']['counts']['followed_by'];
                }
                if (isset($obj['data']['username'])) {
                    $infoArr['instagram_profile_url'] = 'https://www.instagram.com/' . $obj['data']['username'];
                }
            } else {
                throw new Exception("[INSTAGRAM] Запрос с ошибкой");
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
        }

        if (!empty($infoArr)) {
            return $infoArr;
        }
        return false;
    }

    public static function getAuthLink() {
        return "https://api.instagram.com/oauth/authorize/?client_id=" . self::CLIENT_ID . "&redirect_uri=http://" . $GLOBALS['config']['domain'] . "/instaauth&response_type=code";
    }

}
