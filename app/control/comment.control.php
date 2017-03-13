<?php

/**
 * Контроллер комментариев
 */
class CommentControl extends PageControl{
    
    /**
     * @var array Массив допустимых действий
     */
    protected $actions = ['addComment',
                          'removeComment',
                          'updateComment',
                          'getComment',
                          'addLike',
                          'removeLike'];
    /**
     * @var array Массив для входящих данных
     */
    protected $fields = [
        'addComment' => [
            'user_id'   => ['user',     true],
            'text'      => ['text',     true]
        ]
    ];
    
    /**
     * Конструктор. Определяет необходимое действие и запускает его
     */
    public function __construct() {
        parent::__construct();
        $this->model = new CommentModel();
        $this->getModelFields();
        $this->setModelFields();
        switch ($this->action){
            case 'addComment':
                $this->addComment();
            break;
            case 'removeComment':
                return $this->model->removeComment();
            break;
            case 'updateComment':
                return $this->updateComment();
            break;
            case 'getComment':
                return $this->model->getComment();
            break;
            case 'addLike':
                if($this->model->addLikeDislike())
                    echo 'add';
                else
                    echo 'notadd';
            break;
            case 'removeLike':
                if($this->model->removeLikeDislike())
                    echo 'remove';
                else
                    echo 'notremove';
            break;
        }
    }
    /**
     * Добавление нового комментария
     */
    private function addComment() {
        if (AuthModel::get()->isAuthorized()) {
            if ($this->checkVars()) {
                $this->model->text = FILTER::sanitizeHTML( strip_tags( $this->vars['text'] ) );
                $this->model->user_id = $this->vars['user_id'];
                $this->model->author = UserModel::get()->info['id'];
                if ($obj = $this->model->addComment()) {
                    $this->view->data->comment = $obj;
                    $this->view->data->comment->datetime = date('d.m.Y H:i:s');
                    $user = UserModel::get()->handlingUserInfo(UserModel::get()->getInfoById(UserModel::get()->info['id']));
                    $this->view->data->comment->name = $user->name;
                    $this->view->data->comment->avatar = $user->avatar;
                    $this->view->page('comment');
                }
                else {
                    $this->view->error(500);
                }
            }
            else {
                $this->view->data->errors = $this->errors;
                $this->view->error(400, 'comment');
            }
        }
        else {
            $this->view->error(403);
        }
    }
    
    /**
     * Обновление комментария
     */
    private function updateComment(){
        if($obj = $this->model->updateComment())
            return $obj->getText();
        return false;
    }


    /**
     * Устанавливает значения входящих полей в объект модели
     */
    private function setModelFields(){
        if(!empty($this->fields)){
            foreach ($this->fields as $key => $value) {
                $set = 'set'.$key;
                $this->model->$set($value); 
            }
        }
    }
    
    /**
     * Проверка входящих полей на наличие в модели и запись в массив fields
     */
    private function getModelFields(){
        if(!empty($_POST)){
            foreach($_POST as $key => $value){
                if(property_exists('CommentModel', $key)) {
                    $this->fields[ucfirst($key)] = $value;
                }
            }
        }
    }
              
}

