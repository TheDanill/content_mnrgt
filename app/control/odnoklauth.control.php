<?php

/**
 * Контроллер авторизации в одноклассниках
 */
class OdnoklAuthControl extends PageControl {
    public  $model;
    
    public function __construct() {
        parent::__construct();
        if($this->getCode()){
            $this->model = new OdnoklAuthModel($this->code); 
            $this->model->getUserInfo();
            $this->model->authorization();
        }
        $this->view->redirect('/');
    }
 
}
