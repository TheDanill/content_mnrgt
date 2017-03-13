<?php
/**
 * @abstract
 * Абстрактная модель авторизации в соц.сетях
 */
abstract class SocialNetworkAuthModel {
    /**
     * @var object Объект для работы с HTTP запросами
     */
    protected $curl;
    
    /**
     * Конструктор объекта
     */
    public function __construct() {
        AuthModel::get()->session_start();
        $this->curl = new HTTP();
    }
    
    /**
     * Авторизация пользователя
     */
    public function authorization() {
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * @abstract Регистрация пользователя
     */
    abstract protected function registration();
    
    /**
     * @abstract Проверка - зарегистрирован ли пользователь
     */
    abstract protected function isRegistered($userID);
    
    /**
     * Получение ссылки для авторизации
     */
    public static function getAuthLink(){}
}