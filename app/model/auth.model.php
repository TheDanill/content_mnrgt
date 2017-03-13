<?php
/**
 * Модель авторизации
 * 
 * @author Дамир Мухамедшин <damirmuh@gmail.com>
 * @package CorpusManager
 * @subpackage CorpusManagerModels
 * @version 1.0
 */
class AuthModel {
    
    /**
     * Singleton для быстрого доступа к объекту авторизации
     * 
     * @staticvar AuthModel $auth Объект авторизации
     * @return \AuthModel Объект авторизации
     */
    public static function get() {
        static $auth;
        if ($auth === null) {
            $auth = new AuthModel();
        }
        return $auth;
    }
    
    /**
     * @var integer Код ошибки
     */
    public $error = 0;
    
    /**
     * @var integer Код ошибки авторизации
     */
    const AUTH_ERROR = 1;
    
    /**
     * Конструктор объекта авторизации
     * 
     * @param boolean $close Указывет, нужно ли заканчивать работу с сессией после инициализации
     */
    public function __construct($close = false) {
        $this->session_start();
        if ($this->isAuthorized()) {
            if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
                session_destroy();
            }
        }
        if ($close) {
            session_write_close();
        }
    }

    /**
     * Проверяет, авторизован ли пользователь
     * 
     * @return boolean Указывает, авторизован ли пользователь
     */
    public function isAuthorized() {
        return isset($_SESSION['info']);
    }
    
    /**
     * Получить информацию о текушем пользователе
     * 
     * @return boolean Указывает, авторизован ли пользователь
     */
    public function whoIsAuthorized() {
        return isset($_SESSION['info']) ? $_SESSION['info'] : NULL ;
    }
    
    /**
     * Авторизовывает пользователя с указанными логином и паролем
     * 
     * @param string $login Логин
     * @param string $pass Пароль
     * @param boolean $remember Класть ли данные о пользователе в куки
     * @param boolean $close Указывает, нужно ли заканчивать работу с сессией после авторизации
     * @return boolean TRUE, если пользователь успешно авторизован, иначе FALSE
     */
    public function auth($login, $pass, $remember = false , $close = true) {
        if ($result = DB::get()->select("
            SELECT  `users`.`id`,
                    `users`.`email`,
                    `users`.`name`,
                    `users`.`surname`,
                    `users`.`permissions`
            FROM `users`
            WHERE
                `users`.`email` = '" . DB::get()->escape($login) . "'
                AND `users`.`password` = '" . crypt($pass, $GLOBALS['config']['password_salt']) . "'
            LIMIT 1
        ", DB::ASSOC, true)) {
            $this->session_start();
            $_SESSION['info'] = $result;
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            if ($close) {
                session_write_close();
            }
            return true;
        }
        else {
            $this->error = self::AUTH_ERROR;
            return false;
        }
    }
    
    /**
     * Осуществляет "выход" пользователя из системы, удаляя сессию
     */
    public function logout() {
        session_destroy();
    }
    
    /**
     * Начинает работу с сессией, если она еще не была начата
     */
    public function session_start() {
        if (session_status() != PHP_SESSION_ACTIVE) {
            // Устанавливаем время сессии равной год
            $lifetime = 31536000;
            // Проставляем значение в PHP
            ini_set('session.gc_maxlifetime', $lifetime);
            // Устанавливаем значение для сессий
            session_set_cookie_params($lifetime);
            session_start();
        }
    }
    
    /**
     * Отдает список стран
     */
    
    protected function getCountries($fields = [], $code = ''){
        if (empty($fields)) {
            $f = '*';
        } else {
            $f = implode(',' , $fields);
        }
        if (!empty($code)) {
            return DB::get()->select('SELECT '.$f.' FROM `countries` WHERE `country_code` = "'.$code.'" ', DB::ASSOC);
        }
        return DB::get()->select('SELECT '.$f.' FROM `countries`', DB::ASSOC);
    }
    
}