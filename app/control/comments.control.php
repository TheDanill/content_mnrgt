<?php

/**
 * Контроллер комментариев
 */
class CommentsControl extends PageControl {
    /**
     * @var array Массив допустимых действий
     */
    protected $actions = ['getComments',
                          'getLikesDislikes'],
    
            /**
             * @var int Идентификатор пользователя
             */
              $user_id,
            /**
             * @var object Объект модели комментариев
             */
              $model,
            /**
             * @var int Идентификатор комментария
             */
              $comment_id;
    
    /**
     * Конструктор. Определяет действие
     */
    public function __construct() {
        parent::__construct();
        $this->model = new CommentsModel();
        switch ($this->action){
            case 'getComments':
                if($this->getParam('user_id')){
                    print_r($this->model->getComments($this->user_id));
                }
            break;
            case 'getLikesDislikes':
                if($this->getParam('comment_id')){
                    print_r($this->model->getLikesDislikes($this->comment_id));
                }
            break;
        }
    }
    
    /**
     * Получение входящих параметров
     * 
     * @return boolean 
     */
    private function getParam($param){
        if(isset($_GET[$param])){
            $this->$param = $_GET[$param];
            return true;
        }
        return false;
    }
}

