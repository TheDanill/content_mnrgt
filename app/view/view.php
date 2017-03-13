<?php
/**
 * Рендеринг данных.
 * 
 * @author Damir Mukhamedshin <d.muhamedshin@m-artkzn.ru>
 * @copyright (c) 2013, Damir Mukhamedshin
 * @version 1.1 Добавлены дополнительные ошибки HTTP
 */
class View {
    /**
     * Singleton для View.
     * @staticvar View $view
     * @return \View
     */
    public static function get() {
	static $view = null;
	if ($view === null) {
	    $view = new View();
        }
	return $view;
    }
    /**
     *
     * @var array Ошибки HTTP
     */
    private $errors = array(
                400 => 'Bad Request',
                403 => 'Forbidden',
                404 => 'Not Found',
                412 => 'Precondition Failed',
                414 => 'Request Entity Too Large',
                418 => 'I\'m a teapot',
                429 => 'Too Many Requests',
                500 => 'Internal Server Error',
                503 => 'Service Unavailable',
                509 => 'Bandwidth Limit Exceeded'
            );
    /**
     *
     * @var string Тайтл страницы
     */
    public $title,
            /**
             * @var mixed Данные, передаваемые шаблону или странице
             */
	    $data,
            $vars,
            /**
             * @var string Название шаблона, использованного для рендеринга
             */
            $template,
            /**
             * @var mixed Информация об авторизованном пользователе
             */
            $user,
            /**
             * @var string Формат вывода
             */
            $mode;
    /**
     * Конструктор, инициирует переменные.
     */
    public function __construct() {
	$this->getMode();
        $this->user = UserModel::get()->info;
        $this->data = new stdClass();
    }
    /**
     * Получает формат вывода.
     */
    private function getMode() {
        /*if ($_GET['action'] == 404) {
            parse_str($_SERVER['REDIRECT_QUERY_STRING'], $tmpGet);
            $_GET = array_merge($_GET, $tmpGet);
        }*/
	$this->mode = isset($_GET['json']) ? 'json' : 'html';
    }
    /**
     * Рендерит ошибку HTTP.
     * @param integer $code Код ошибки HTTP.
     */
    public function error($code, $template = null) {
	$this->title = 'Произошла ошибка';
        if ($template !== null) {
            $this->template = $template;
        }
        $this->data->error_code = ShieldModel::get()->navigation['error'] = $code;
        header('HTTP/1.1 ' . $code . ' ' . $this->errors[$code]);
        header('Status: ' . $code . ' ' . $this->errors[$code]);
        if ($this->mode == 'html') {
            header('Content-type: text/html; charset=utf-8');
            if ($template === null) {
                include_once _BASEDIR . 'templates/' . $code . '.php';
            }
            else {
                include_once _BASEDIR . 'templates/' . $template . '.php';
            }
        }
        else {
            $this->json();
        }
    }
    /**
     * Печатает данные в JSON-формате.
     */
    public function json($data = null) {
	header('Content-type: application/json; charset=utf-8');
        if (is_null($data)) {
            if ($this->mode == 'json') {
                $key = $_GET['json'];
            }
            else {
                $key = false;
            }
            if ($key && isset($this->$key)) {
                echo json_encode($this->$key);
            }
            else {
                echo json_encode(array(
                    'title' => $this->title,
                    'user' => $this->user,
                    'data' => $this->data,
                    'vars' => $this->vars,
                    'template' => $this->template
                ));
            }
        }
        else {
            echo json_encode($data);
        }
    }
    /**
     * Рендерит страницу с использованием указанного шаблона.
     * @param string $template Имя шаблона и имя файла в папке templates
     */
    public function page($template) {
        //Timer::get()->start('View: ' . $template);
        $this->template = $template;
        if ($this->mode == 'html') {
            header('Content-type: text/html; charset=utf-8');
            include_once _BASEDIR . 'templates/' . $template . '.php';
        }
	else {
	    $this->json();
        }
       // Timer::get()->stop('View: ' . $template);
    }
    /**
     * Осуществляет редирект на указанную страницу.
     * @param string $url
     */
    public function redirect($url) {
        if ($this->mode == 'html') {
            header("Location: " . $url);
        }
        else {
            $this->json(array('location' => $url));
        }
    }
    
}