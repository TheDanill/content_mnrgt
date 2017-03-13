<?php

/**
 * Контроллер авторизации через Facebook
 */

class FbAuthControl extends PageControl{
    public function __construct() {
        parent::__construct();
        if($this->getCode()) {
            $fbauth = new FbAuthModel();
            $fbauth->code = $this->code;
            $fbauth->authorization();
        }
        $this->view->redirect('/');
    }
    
}
