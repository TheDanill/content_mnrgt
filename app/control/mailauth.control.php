<?php
/**
 * Контроллер авторизации в Mail.ru
 */
class MailAuthControl extends PageControl{
    protected $model;
    
    public function __construct() {
        parent::__construct();
        if($this->getCode()){
            $this->model = new MailAuthModel($this->code);
            $this->model->getUserInfo();
            $this->model->authorization();
        }
        $this->view->redirect('/');
    }
    

}

