<?php

/**
 * Контроллер авторизации через Google
 */
class GoogleAuthControl extends PageControl{

    public function __construct() {
        parent::__construct();
        if($this->getCode()) {
            $googleAuth = new GoogleAuthModel();
            $googleAuth->code = $this->code;
            $googleAuth->authorization();
        }
        $this->view->redirect('/');
    }    

}

