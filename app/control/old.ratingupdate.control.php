<?php

class RatingUpdateControl extends PageControl{
    private $model,
            $userID;
    protected $actions = [
                'updateRating'
            ];
    public function __construct() {
        parent::__construct();
        switch ($this->action){ 
            case 'updateRating':
                $this->updateRating();
            break;
        }
    }

    private function updateRating(){
        $this->getUserID();
        $this->model = new RatingUpdateModel($this->userID);
        $this->model->updateRating();
    }
    
    private function getUserID(){
        if(isset($_GET['userID'])){
            $this->userID = $_GET['userID'];
        }
    }
}

