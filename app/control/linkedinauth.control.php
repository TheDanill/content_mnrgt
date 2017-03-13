<?php

/**
 * Контроллер авторизации в LinkedIn
 */

class LinkedInAuthControl extends PageControl{
    private $model;
    
    public function __construct() {
        parent::__construct();
        $this->model = new LinkedInAuthModel();
        if($this->getCode()){
            $this->model->code = $this->code;
            $this->model->authorization();
        }
        $this->view->redirect('/');
    }
}

