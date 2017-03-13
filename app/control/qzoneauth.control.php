<?php
/**
 * Контроллер авторизации в соц.сети Qzone
 */
class QzoneAuthControl extends PageControl{
    private $model;
    
    public function __construct() {
        parent::__construct();
        if($this->getCode()) {
            $this->model = new QzoneAuthModel();
            $this->model->code = $this->code;
            $this->model->authorization();
        }
        $this->view->redirect('/');    
    }
}

