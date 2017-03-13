<?php

/**
 * Контроллер городов
 */

class CityControl extends PageControl {
    protected $actions = [
        'getCities'
    ];
    
    public function __construct() {
        parent::__construct();
        $citysModel = new CityModel();
        switch ($this->action){
            case 'getCities':
                $data = $citysModel->getCities($this->getCountryId());
                $this->view->json($data);
            break;
        }
    }
    
    /**
     * Получение идентификатора страны, если он имеется
     */
    public function getCountryId() {
        if(isset($_POST['countryId']) && !empty($_POST['countryId'])) {
            return $_POST['countryId'];
        }
        return;
    }
}