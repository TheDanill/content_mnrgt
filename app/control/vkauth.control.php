<?php
/**
 * Контроллер авторизации в Vk
 */
class VkAuthControl extends PageControl{
    
    public function __construct() {
        parent::__construct();
        if ( $this->getCode() ) {

            $vkAuthModel = new VkAuthModel();
            $vkAuthModel->code = $this->code;
            $isAuth = $vkAuthModel->authorization();
            
            if ( $isAuth ) {
                $this->view->redirect('/id' . $isAuth);
            } else {
                $this->view->redirect('/');
            }
            
        } else {
            
            $this->view->redirect('/');
        
        }
        
    }    
    
}

