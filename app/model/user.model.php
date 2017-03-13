<?php

class UserModel implements ObjectModel {

    /**
     * @var integer Уровень пользователя "гость"
     */
    const GUEST = 0,
            /**
             * @var integer Уровень пользователя "обычный"
             */
            USER = 1,
            /**
             * @var integer Уровень пользователя "проверенный"
             */
            CHECKED = 2,
            /**
             * @var integer Уровень пользователя "редактор"
             */
            EDITOR = 3,
            /**
             * @var integer Уровень пользователя "администратор"
             */
            ADMIN = 4;

    /**
     * Singleton для объекта пользователя
     * 
     * @staticvar UserModel $user Объект пользователя
     * @return \UserModel Объект пользователя
     */
    public static function get() {
        static $user;
        if ($user === null) {
            $user = new UserModel();
        }
        return $user;
    }

    /**
     * @var integer Код ошибки
     */
    public $error,
            /**
             * @var stdClass Информация о пользователе
             */
            $info;

    /**
     * @var integer Код ошибки "пользователь не авторизован"
     */
    const USER_NOT_AUTH = 1;

    /**
     * Конструктор объекта пользователя
     * 
     * @param integer $id Идентификатор пользователя
     */
    public function __construct($id = null) {
        if ($id === null) {
            if (AuthModel::get()->isAuthorized()) {
                $this->getInfo();
            } else {
                $this->error = self::USER_NOT_AUTH;
                $this->info = new stdClass();
                $this->info->permissions = 0;
            }
        } else {
            $this->info = $this->getInfoById($id);
        }
    }

    /**
     * Получение информации о пользователе
     * 
     * @return boolean|stdClass Информация о пользователе или FALSE, если пользователь не авторизован
     */
    public function getInfo() {
        if ($this->error) {
            return false;
        }
        if ($this->info === null) {
            return $this->info = $_SESSION['info'];
        } else {
            return $this->info;
        }
    }

    /**
     * Получение информации о пользователе по идентификатору
     * 
     * @param integer $id Идентификатор пользователя
     * @return stdClass Информация о пользователе
     */
    public function getInfoById($id, $status = 'active') {
        return DB::get()->select($this->getSelectQuery($id, $status), DB::OBJECT, true);
    }
    
    public function issetUser($id, $status = 'active') {
        return (true && DB::get()->select("SELECT 1 FROM `users` WHERE `users`.`id` = " . DB::get()->escape($id, true) . " AND `users`.`status` = " . DB::get()->escape($status, true)));
    }
    
    public function getSelectQuery($id, $status) {
        return '
            SELECT
                `u`.`id`,
                `u`.`name`,
                `u`.`surname`,
                `u`.`name_en`,
                `u`.`surname_en`,
                `u`.`nickname`,
                `u`.`birthday`,
                `countries`.`country_name` AS `country`,
                `u`.`country_id`,
                `citys`.`name` AS `city`,
                `u`.`city_id`,
                `u`.`gender`,
                `u`.`default_lang`,
                `u`.`avatar`,
                `u`.`permissions` AS `verified`,
                `u`.`wikipedia`,
                `u`.`wikipedia_description`,
                `u`.`description`,
                `u`.`likes`,
                `u`.`dislikes`,
                `u`.`google_profile_url`,
                `u`.`instagram_profile_url`,
                `u`.`facebook_profile_url`,
                `u`.`vkontakte_profile_url`,
                `u`.`odnoklassniki_profile_url`,
                `u`.`mailru_profile_url`,
                `u`.`linkedin_profile_url`,
                `u`.`qzone_profile_url`,
                `u`.`renren_profile_url`,
                `u`.`twitter_profile_url`,
                `u`.`weibo_profile_url`,
                `u`.`rating`,
                `u`.`percentage_profile` AS `percentage`, 
                (SELECT COUNT(*) + 1
                    FROM `users` `u2`
                    WHERE   `u2`.`rating` > `u`.`rating`
                            OR (`u2`.`rating` = `u`.`rating` AND `u2`.`id` > `u`.`id`)) AS `place`,
                `u`.`last_place`,
                `activity`.`name` AS `activity`,
                `u`.`activity_id`,
                IF(`u`.`permissions` = ' . DB::get()->escape(self::CHECKED) . ', 1, 0) AS `checked`
            FROM `users` `u`
            LEFT JOIN `countries` ON (`countries`.`id` = `u`.`country_id`)
            LEFT JOIN `citys` ON (`citys`.`id` = `u`.`city_id`)
            LEFT JOIN `activity` ON (`activity`.`id` = `u`.`activity_id`)
            WHERE `u`.`id` = "' . $id . '" AND `u`.`status` = "' . $status . '"
        ';
    }

    /**
     * Получение информации о пользователе по идентификатору в одной из социальных сетей
     * 
     * @param integer $id Идентификатор пользователя в социальной сети
     * @param string $name Название поля соц.сети в БД
     * @return stdClass Информация о пользователе
     */
    public function getInfoBySocId($id, $name) {

        return DB::get()->select('SELECT  `users`.`id`,`users`.`name`,`users`.`surname`,`users`.`nickname`,`users`.`status`,`users`.`default_lang`,`users`.`permissions` FROM `users` WHERE `users`.`' . $name . '` = ' . $id . '', DB::ASSOC, true);
        
    }

    /**
     * Добавление нового пользователя
     * 
     * @param array $user Массив параметров нового пользователя
     * @param boolean $now Указывает, нужно ли добавлять пользователя прямо сейчас, или добавить в очередь
     * @return integer Идентификатор нового пользователя
     */
    public function add(array $user, $now = true) {
        /* $insert = [
          null,
          $user['login'],
          md5($user['pass']),
          htmlspecialchars($user['name']),
          $user['permissions'],
          isset($user['status']) ? $user['status'] : 'active'
          ]; */

        // default user permission
        $user['permissions'] = isset($user['permissions']) ? $user['permissions'] : 1;
        // default user status
        $user['status'] = isset($user['status']) ? $user['status'] : 'active';
        $user['password'] = isset($user['password']) ? crypt($user['password'], $GLOBALS['config']['password_salt']) : '';
        $user['percentage_profile'] = $this->calcFillProfile($user);
        if ($now) {
            return DB::get()->insert('users', $user);
        } else {
            DB::get()->addValues('users', $user);
        }
    }

    /**
     * Редактирование пользователя
     * 
     * @param array $user Массив новых параметров пользователя
     * @param boolean $now Указывает, нужно ли редактировать пользователя прямо сейчас, или добавить в очередь
     * @return mixed Результат редактирования пользователя
     */
    public function edit(array $user, $now = true) {
        if (!isset($user['id'])) {
            $user['id'] = $this->info['id'];
        }
        if (isset($user['avatar']) && isset($user['avatar']['tmp_name'])) {
            $name = dechex(10000000000 - $user['id']) . "-" . mb_strcut(md5(PHP_INT_MAX / $user['id']), 0, 5);
            Image::save($user['avatar']['tmp_name'], 200, 200, _DIR_IMAGES . $name . ".jpg", true);
            Image::save(_DIR_IMAGES . $name . ".jpg", 40, 40, _DIR_IMAGES . "40x40" . DIRECTORY_SEPARATOR . $name . ".jpg", false);
            $user['avatar'] = $name . ".jpg";
        }
        $q = "
            UPDATE  `users`
            SET `users`.`name` = " . DB::get()->escape($user['name'], true) . ",
                `users`.`surname` = " . DB::get()->escape($user['surname'], true) . ",
                `users`.`nickname` = " . DB::get()->escape($user['nickname'], true) . ",
                " . (isset($user['avatar']) ? "`users`.`avatar` = " . DB::get()->escape($user['avatar'], true) . "," : "") . "
                `users`.`name_en` = " . DB::get()->escape($user['name_en'], true) . ",
                `users`.`surname_en` = " . DB::get()->escape($user['surname_en'], true) . ",
                `users`.`birthday` = " . DB::get()->escape($user['birthday'], true) . ",
                `users`.`last_edit` = " . DB::get()->escape( date('Y-m-d H:i:s'), true) . ",
                `users`.`activity_id` = " . DB::get()->escape($user['activity_id'], true) . ",
                `users`.`country_id` = " . DB::get()->escape($user['country_id'], true) . ",
                `users`.`city_id` = " . DB::get()->escape($user['city_id'], true) . ",
                `users`.`default_lang` = " . DB::get()->escape($user['default_lang'], true) . ",
                `users`.`gender` = " . DB::get()->escape($user['gender'], true) . ",
                `users`.`description` = " . DB::get()->escape($user['description'], true) . ",
                `users`.`wikipedia` = " . DB::get()->escape($user['wikipedia'], true) . ",
                `users`.`wikipedia_description` = " . DB::get()->escape($user['wikipedia_description'], true) . ",
                `users`.`percentage_profile` = " . $this->calcFillProfile($user) . "
            WHERE `users`.`id` = " . DB::get()->escape($user['id'], true) . "
        ";
        if ($now) {
            $return = DB::get()->query($q);
            Cache::redis()->delete('query_' . DB::OBJECT . '_' . md5($this->getSelectQuery($user['id'], 'active')));
            return $return;
        } else {
            DB::get()->addQuery($q);
        }
    }

    /**
     * Активация пользователя
     * 
     * @param integer|null $id Идентификатор пользователя, может быть указано NULL, тогда будет активирован пользователь, для которого создан объект
     * @param boolean $now Указывает, нужно ли активировать пользователя прямо сейчас, или добавить в очередь
     * @return mixed Результат активации пользователя
     */
    public function activate($id = null, $now = true) {
        if ($id === null) {
            $id = $this->info->id;
        }
        $q = "UPDATE `users` SET `status` = 'active' WHERE `id` = " . $id;
        if ($now) {
            return DB::get()->query($q);
        } else {
            DB::get()->addQuery($q);
        }
    }

    /**
     * Обработка пользовательской информации для вывода на странице
     * 
     * @param object $data Объект с данными пользователя
     * @return object $data Объект с обработанными данными 
     */
    public function handlingUserInfo($data) {
        
        if ( ! empty( $data ) ) {

            if ( Lang::get()->lang === 'ru_RU' ) {
                $data->name = $data->name . ' ' . (!empty($data->nickname) ? $data->nickname . ' ' : '') . $data->surname;
            } else {
                $data->name = $data->name_en . ' ' . (!empty($data->nickname) ? $data->nickname . ' ' : '') . $data->surname_en;
            }

//        if ( empty( $data->name_en) && empty($data->surname_en) ) {
//            $data->name = $data->name_en . ' ' . $data->surname_en;
//        } elseif ( empty($data->name ) && empty( $data->surname ) ) {
//            $data->name = $data->name . ' ' . $data->surname;   
//        } elseif ( empty($data->nickname) ) {
//            $data->name = $data->nickname; 
//        }

            $data->facebook_form = [];
            $data->instagram_form = [];
            $data->twitter_form = [];
            $data->other_socnetwork = [];

            if (!empty($data->google_profile_url)) {
                $data->facebook_form[0]['url'] = $data->google_profile_url;
                $data->facebook_form[0]['name'] = 'google';
            }

            if (!empty($data->instagram_profile_url)) {
                $data->instagram_form[0]['url'] = $data->instagram_profile_url;
                $data->instagram_form[0]['name'] = 'instagram';
            }

            if (!empty($data->facebook_profile_url)) {
                $data->facebook_form[0]['url'] = $data->facebook_profile_url;
                $data->facebook_form[0]['name'] = 'facebook';
            }

            if (!empty($data->vkontakte_profile_url)) {
                $data->facebook_form[0]['url'] = $data->vkontakte_profile_url;
                $data->facebook_form[0]['name'] = 'vkontakte';
            }

            if (!empty($data->odnoklassniki_profile_url)) {
                $data->facebook_form[0]['url'] = $data->odnoklassniki_profile_url;
                $data->facebook_form[0]['name'] = 'odnoklassniki';
            }

            if (!empty($data->mailru_profile_url)) {
                $data->facebook_form[0]['url'] = $data->mailru_profile_url;
                $data->facebook_form[0]['name'] = 'mailru';
            }

            if (!empty($data->linkedid_profile_url)) {
                $data->other_socnetwork[0]['url'] = $data->linkedin_profile_url;
                $data->other_socnetwork[0]['name'] = 'linkedin';
            }

            if (!empty($data->qzone_profile_url)) {
                $data->facebook_form[0]['url'] = $data->qzone_profile_url;
                $data->facebook_form[0]['name'] = 'qzone';
            }

            if (!empty($data->renren_profile_url)) {
                $data->facebook_form[0]['url'] = $data->renren_profile_url;
                $data->facebook_form[0]['name'] = 'renren';
            }

            if (!empty($data->twitter_profile_url)) {
                $data->twitter_form[0]['url'] = $data->twitter_profile_url;
                $data->twitter_form[0]['name'] = 'twitter';
            }

            if (!empty($data->weibo_profile_url)) {
                $data->facebook_form[0]['url'] = $data->weibo_profile_url;
                $data->facebook_form[0]['name'] = 'weibo';
            }

            return $data;
        } else {
            return false;
        }
    }

    /**
     * "Бан" пользователя
     * 
     * @param integer|null $id Идентификатор пользователя, может быть указано NULL, тогда будет "забанен" пользователь, для которого создан объект
     * @param boolean $now Указывает, нужно ли "забанить" пользователя прямо сейчас, или добавить в очередь
     * @return mixed Результат "бана" пользователя
     */
    public function ban($id = null, $now = true) {
        if ($id === null) {
            $id = $this->info->id;
        }
        $q = "UPDATE `users` SET `status` = 'banned' WHERE `id` = " . $id;
        if ($now) {
            return DB::get()->query($q);
        } else {
            DB::get()->addQuery($q);
        }
    }

    /**
     * Добавление пользователя в корзину
     * 
     * @param integer|null $id Идентификатор пользователя, может быть указано NULL, тогда пользователь, для которого создан объект, будет добавлен в корзину
     * @param boolean $now Указывает, нужно ли добавить пользователя в корзину прямо сейчас, или добавить в очередь
     * @return mixed Результат добавления пользователя в корзину
     */
    public function trash($id = null, $now = true) {
        if ($id === null) {
            $id = $this->info->id;
        }
        $q = "UPDATE `users` SET `status` = 'trash' WHERE `id` = " . $id;
        if ($now) {
            return DB::get()->query($q);
        } else {
            DB::get()->addQuery($q);
        }
    }

    /**
     * Удаление пользователя
     * 
     * @param integer|null $id Идентификатор пользователя, может быть указано NULL, тогда будет удален пользователь, для которого создан объект
     * @param boolean $now Указывает, нужно ли удалить пользователя прямо сейчас, или добавить в очередь
     * @return mixed Результат удаления пользователя
     */
    public function remove($now = true) {
        $q = "DELETE FROM `users` WHERE `id` = " . $this->info->id;
        if ($now) {
            return DB::get()->query($q);
        } else {
            DB::get()->addQuery($q);
        }
    }
    
    /**
     * Вычисляем процент заполненности профиля
     * 
     * @return mixed Процент заполнения пользователям профиля
     */
    
    private function calcFillProfile($user) {
        $count_criteria = 8;
        $counter = 0;
        if (!empty($user['name'])) { $counter++; }
        if (!empty($user['surname']))  { $counter++; }
        if (!empty($user['birthday'])) { $counter++; }
        if (!empty($user['country_id'])) { $counter++; }
        if (!empty($user['city_id'])) { $counter++; }
        if (!empty($user['gender'])) { $counter++; }
//        if (!empty($user['avatar'])) { $counter++; }
        if (!empty($user['description'])) { $counter++; }
        if (!empty($user['activity_id'])) { $counter++; }
        if ($counter == 0) {
            return null;
        } else {
            var_dump($counter);
            return round( ( $counter / $count_criteria ) * 100 );
        }
    }

}
