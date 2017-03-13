<?php

/**
 * Контроллер авторизации в Twitter
 */
class TwitterAuthControl extends PageControl{
            /**
             * @var string Токен авторизации
             */
    private $oauth_token,
            /**
             * @var string Верификатор авторизации
             */
            $oauth_verifier;

    /**
     * Конструктор определяет и запускает необходимые действия
     */    
    public function __construct() {
        parent::__construct();
        $authModel = new TwitterAuthModel('','','');
        if($this->auth_token = $authModel->getAuthInfo() && !$this->getRequestInfo()){
            $this->view->redirect('https://api.twitter.com/oauth/authorize?oauth_token='.$authModel->authInfo['oauth_token']);
        }
        if($this->getRequestInfo()){
            $authModel2 = new TwitterAuthModel($this->oauth_token, $this->oauth_verifier, $authModel->authInfo['oauth_token_secret']);
            $authModel2->getAuthInfo('true', $this->oauth_token, $this->oauth_verifier);
            $authModel2->authorization();
            $this->view->redirect('/');
        }
    }
    
    /**
     * Получение входящих параметров авторизации после того, как пользователь перешел на callback url
     * 
     * @return boolean Результат выполнения операции
     */
    private function getRequestInfo(){
        if(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
            $this->oauth_token = $_GET['oauth_token'];
            $this->oauth_verifier = $_GET['oauth_verifier'];
            return true;
        }
        return false;
    }
}
