<?php
    
/**
 * Контроллер авторизации в Instagram
 */
class InstaAuthControl extends PageControl{
    
    public function __construct() {
        parent::__construct();
        if($this->getCode()) {
            $instaAuth = new InstaAuthModel();
            $instaAuth->code = $this->code;
            $instaAuth->authorization();
        }
        $this->view->redirect('/');
    }
    
}
