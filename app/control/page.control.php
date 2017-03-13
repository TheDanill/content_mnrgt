<?php
/**
 * Контроллер страниц
 * 
 * @author Дамир Мухамедшин <damirmuh@gmail.com>
 * @package CorpusManager
 * @subpackage CorpusManagerControllers
 * @abstract
 * @uses View Вид
 * @version 1.0
 */
abstract class PageControl {
    
    use RequestControl;
    
    /**
     * @var integer Номер страницы
     */
    protected   $page,
                /**
                 * @var integer Количество объектов на странице
                 */
                $per_page,
                /**
                 * @var View Вид
                 */
                $view,
                /**
                 * @var string Действие, требуемое от системы
                 */
                $action,
                /**
                 * @var array Массив допустимых действий
                 */
                $actions = [],
                /**
                 * @var string Тип сортировки
                 */
                $order,
                /**
                 * @var array Массив допустимых типов сортировки
                 */
                $orders = [],
                /**
                 *  @var integer Код авторизации соцсетей
                 */
                $code,
                /**
                 * @var object Объект модели
                 */
                $model;
    
    /**
     * Конструктор контроллера страниц
     * 
     * @uses View Вид
     */
    public function __construct() {
        $this->getPage();
        $this->getPerPage();
        $this->getAction();
        
        $this->view = View::get();
        $this->view->data->page = $this->page;
        $this->view->data->per_page = $this->per_page;
        $this->view->data->order = $this->order;
        
    }
    
    /**
     * Получение номера страницы
     */
    public function getPage() {
        $this->page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    }
    
    /**
     * Получение количества результатов на страницу
     */
    public function getPerPage() {
        $get = isset($_POST['per_page']) ? $_POST : $_GET;
        $this->per_page = isset($get['per_page']) && is_numeric($get['per_page']) && (int)$get['per_page'] > 0 && (int)$get['per_page'] <= $GLOBALS['config']['max_per_page'] ? (int)$get['per_page'] : (isset($get['per_page']) && $get['per_page'] == 'all' && AuthModel::get()->isAuthorized() && UserModel::get()->info->permissions >= UserModel::EDITOR ? 'all' : $GLOBALS['config']['per_page']);
       // $this->per_page = isset($get['per_page']) && is_numeric($get['per_page']) && (int)$get['per_page'] > 0 && (int)$get['per_page'] <= $GLOBALS['config']['max_per_page'] ? (int)$get['per_page'] : (isset($get['per_page']) && $get['per_page'] == 'all' ? 'all' : $GLOBALS['config']['per_page']);
    }
    
    /**
     * Получение запрашиваемого действия
     */
    protected function getAction() {
        $this->action = isset($_GET['do']) && in_array($_GET['do'], $this->actions) ? $_GET['do'] : '';
    }
    
    /**
     * Получение типа сортировки
     * 
     * @param boolean $required Флаг, указывающий на обязательность наличия типа сортировки (TRUE - обязательно, FALSE - необязательно)
     */
    protected function getOrder($required = false) {
        $this->order = isset($_GET['order']) && in_array($_GET['order'], $this->orders) ? $_GET['order'] : ($required ? current($this->orders) : '');
    }
    /**
     * Получение и присвоение кода авторизации соц сетей
     * @return boolean
     */
    protected function getCode(){
            if(isset($_GET['code'])){
                $this->code = $_GET['code'];
                return true;
            }
            return false;
    }
    
    /*
     * Присвоение view ссылок на авторизацию через соц.сети 
     */
    protected function addSocLinks() {
        $this->view->vars['vklink'] = VkAuthModel::getAuthLink();
        $this->view->vars['fblink'] = FbAuthModel::getAuthLink();
        $this->view->vars['instalink'] = InstaAuthModel::getAuthLink();
        $this->view->vars['glink'] = GoogleAuthModel::getAuthLink();
        $this->view->vars['twlink'] = TwitterAuthModel::getAuthLink();
        $this->view->vars['oklink'] = OdnoklAuthModel::getAuthLink();
        $this->view->vars['maillink'] = MailAuthModel::getAuthLink();
//        $this->view->vars['linlink'] = LinkedInAuthModel::getAuthLink(); 
//        $this->view->vars['weibolink'] = WeiboAuthModel::getAuthLink();
//        $this->view->vars['qzonelink'] = QzoneAuthModel::getAuthLink();
    }
    
}