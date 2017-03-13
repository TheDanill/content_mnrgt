<?php

/**
 * Контроллер авторизации в соц. сети Weibo
 */
class WeiboAuthControl extends PageControl{
    private $model;
    public function __construct() {
        parent::__construct();
        if($this->getCode()) {
            $this->model = new WeiboAuthModel($this->code);  
        }
        $this->view->redirect('/');
    }
}