<?php

/**
 * Контроллер лайков - дизлайков пользователей
 */
class UserLikesControl extends PageControl {
    /**
     * @var array Допустимые действия
     */
    protected $actions = ['addLike', 'removeLike'];
    
    /**
     * Определяет действие и запускает его
     */
    public function __construct() {
        parent::__construct();
        if (isset($_POST['userId']) && isset ($_POST['subject'])) {
            switch ($this->action) {
                case 'addLike':
                    // Третья функция в аргументах для проверки, что пользователь авторизован
                    if (isset($_POST['userId']) && isset($_POST['subject']) && UserModel::get()->getInfo()) {
                        $likesModel = new UserLikesModel($_POST['userId'], UserModel::get()->info['id'], $_POST['subject']);
                        if ($number = $likesModel->addLike()) {
                            $result = ['status' => true, 'value' => $number];
                        } else {
                            $result = ['status' => false];
                        }
                        $this->view->json($result);
                    } else {
                        $result = ['status' => false];
                    }
                    break;
                case 'removeLike':
                    // Третья функция в аргументах для проверки, что пользователь авторизован
                    if (isset($_POST['userId']) && isset($_POST['subject']) && UserModel::get()->getInfo()) {
                        $likesModel = new UserLikesModel($_POST['userId'], UserModel::get()->info['id'], $_POST['subject']);
                        $number = $likesModel->removeLike();
                        if ($number !== false) {
                            $result = ['status' => true, 'value' => $number];
                        } else {
                            $result = false;
                        }
                        $this->view->json($result);
                    } else {
                        $result = ['status' => false];
                    }
                    break;
            }
        }
    }
    
}