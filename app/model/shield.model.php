<?php
/**
 * Модель защиты
 * 
 * @author Дамир Мухамедшин <damirmuh@gmail.com>
 * @package CorpusManager
 * @subpackage CorpusManagerModels
 * @version 1.0
 */
class ShieldModel {
    
    /**
     * @var integer Уровень угрозы "доверенный"
     */
    const   TRUSTED     = 0,
            /**
             * @var integer Уровень угрозы "любопытный"
             */
            CURIOUS     = 1,
            /**
             * @var integer Уровень угрозы "подозрительный"
             */
            SUSPICIOUS  = 2,
            /**
             * @var integer Уровень угрозы "хакер"
             */
            HACKER      = 3,
            /**
             * @var integer Уровень угрозы "DDoS"
             */
            DDOS        = 4;
    
    /**
     * Singleton для объекта защиты
     * 
     * @staticvar ShieldModel $shield Объект защиты
     * @param string|null $action Запрашиваемое действие
     * @return \ShieldModel Объект защиты
     * @throws Exception Выдается, если не передано действие
     */
    public static function get($action = false) {
        static $shield;
        if ($shield === null) {
            if ($action === false) {
                throw new Exception("Please send action to Shield for the first use.");
            }
            else {
                $shield = new ShieldModel($action);
            }
        }
        return $shield;
    }

    /**
     * @var array Массив соответствий между действиями и необходимыми правами пользователя для их выполнения
     */
    private $permissions = [
                null            => UserModel::GUEST,
                'static'        => UserModel::GUEST,
                'search'        => UserModel::GUEST,
                'context'       => UserModel::GUEST,
                'auth'          => UserModel::GUEST,
                'document'      => UserModel::EDITOR,
                'sentence'      => UserModel::EDITOR,
                'word'          => UserModel::EDITOR,
                'staticedit'    => UserModel::ADMIN,
                'stat'          => UserModel::ADMIN,
                'security'      => UserModel::ADMIN,
                'usersedit'     => UserModel::ADMIN
            ];
    
    /**
     * @var string|null Запрашиваемое действие 
     */
    private $action;
    
    /**
     * @var boolean Указывает, разрешено ли пользователю выполнение данного действия 
     */
    public  $permitted = true,
            /**
             * @var integer Уровень угрозы
             */
            $threat_level = self::TRUSTED,
            /**
             * @var integer Код ошибки HTTP
             */
            $http_error_code = 200,
            /**
             * @var string URL, по которому необходимо перенаправить пользователя
             */
            $redirect_url = "http://google.com/404",
            /**
             * @var array Массив с информацией о навигации
             */
            $navigation = [];


    /**
     * Конструктор объекта защиты
     * 
     * @param string|null $action Запрашиваемое действие
     */
    public function __construct($action) {
        $this->action = $action;
        $this->checkPermission();
        $this->checkBan();
        $this->registerNavigation();
    }
    
    /**
     * Проверка прав доступа
     */
    private function checkPermission() {
        if (isset($this->permissions[$this->action])) {
            if (is_array(UserModel::get()->info)) {
                $this->permitted = UserModel::get()->info['permissions'] >= $this->permissions[$this->action];
            } else if (is_object(UserModel::get()->info)) {
                $this->permitted = UserModel::get()->info->permissions >= $this->permissions[$this->action];
            }
            if (!$this->permitted) {
                $this->http_error_code = 403;
            }
        }
    }
    
    /**
     * Проверка, не забанен ли пользователь
     * 
     * @return integer Уровень угрозы
     */
    private function checkBan() {
        if ($banned = Cache::redis()->get('banned_' . ip2long($_SERVER['REMOTE_ADDR']))) {
            $this->threat_level = $banned[0];
            $this->http_error_code = $banned[1];
        }
        return $this->threat_level;
    }
    
    /**
     * Запись навигации
     */
    private function registerNavigation() {
        $this->navigation = [
            'ip' => ip2long($_SERVER['REMOTE_ADDR']),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'uri' => $_SERVER['REQUEST_URI'],
            'action' => $this->action,
            'time' => $_SERVER['REQUEST_TIME'],
            'permitted' => $this->permitted
        ];
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->navigation['referer'] = $_SERVER['HTTP_REFERER'];
        }
        if (isset($_SERVER['HTTP_VIA']) || isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->navigation['is_proxy'] = true;
        }
        if (isset($_GET['do'])) {
            $this->navigation['do'] = $_GET['do'];
        }
        if (isset(UserModel::get()->info->id)) {
            $this->navigation['user'] = UserModel::get()->info->id;
        }
    }
    
    /**
     * Сохранение навигации в очередь
     */
    public function saveNavigation() {
        Queue::q()->set('navigation_history', $this->navigation);
    }

    /**
     * Указывает, может ли пользователь просматривать данную страницу
     * 
     * @return boolean Может ли пользователь просматривать данную страницу
     */
    public function userCanSee() {
        return $this->permitted && $this->threat_level === self::TRUSTED;
    }
    
}