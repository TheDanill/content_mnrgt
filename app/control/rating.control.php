<?php
/**
 * Контроллер рейтинга 
 */
class RatingControl extends PageControl {
    
    /**
     * @var object Модель рейтинга
     */
    protected   $model,
                /**
                 * @var int Идентификатор пользователя
                 */
                $userID;
    /**
     * @var array Массив допустимых действий
     */
    protected $actions = [
                'calculateRating',
                'updateRating',
                'recalcAllUsers',
                'updateAllUsers',
                'recalcOneUser'
            ];
    
    /**
     * Конструктор для определения и выполнения действия
     */
    public function __construct() {
        echo '<pre>';
        parent::__construct();
        $this->getUserID();
        $this->model = new RatingModel($this->userID);
        switch ($this->action){
            case 'calculateRating':
                echo $this->model->calculateRating($this->userID);
                break;
            case 'updateRating':
                $this->model->updateRating($this->userID);
                break;
            case 'recalcAllUsers':
                RatingModel::recalcAllUsers();
                break;
            case 'updateAllUsers':
                RatingModel::updateAllUsers();
                break;
            case 'recalcOneUser':
                if (isset($this->userID) && !empty($this->userID)) {
                    $this->model->recalcOneUser($this->userID);
                }
                break;
        }
    }
    /**
     * Присвоение идентификатора пользователя переменной userId
     * 
     * @return boolean Результат выполения операции
     */
    private function getUserID(){
        if ($_GET['userID']) {
            $this->userID = $_GET['userID'];
            return true;
        }
        return false;
    }
}

