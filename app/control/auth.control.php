<?php
/**
 * Контроллер авторизации
 */

class AuthControl extends PageControl {
    
    /**
     * @var array Доступные действия 
     */
    protected $actions = [
        'login',
        'logout',
        'register'
    ];
    
    /**
     * Конструктор. Определяет и запускает необходимое действие
     */
    public function __construct() {
        parent::__construct();
        if ($this->action == ''){
            $this->view->redirect('/'); 
        }
        if ($this->action == 'login') {
            if(isset($_POST['login'])) {
                if (AuthModel::get()->auth($_POST['login'], $_POST['password'], isset($_POST['rememberme']))) {
                    $this->view->redirect('/');
                }
                else {
                    $this->view->error(403, 'index');
                }
            }
            else {
                $this->view->error(403, 'index');
            }
        }
        if ($this->action == 'logout'){
            $auth = AuthModel::get()->logout();
            $this->view->redirect('/');
        }
        if ($this->action == 'register'){
            
        }
    }
}