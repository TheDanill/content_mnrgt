<?php

/**
 * Контроллер словаря
 */
class VocabularyControl extends PageControl {
    /**
     * @var string Тип словаря (Нецензурные, недопустимыеы)
     */
    protected $type; 
    
    /**
     * @var array Допустимые действия
     */
    protected $actions = [
        'vocabulars',
        'addVocabulary',
        'checkText'
    ];
    
    /**
     * Конструктор. Определяет и запускает действие
     */

    public function __construct() {
        parent::__construct();
        //echo '<pre>';
        switch($this->action){
            case 'vocabulars':
                $this->view->page('vocs');
            break;
            case 'addVocabulary':
                if($this->getType()){
                    $voc = new Vocabulary($this->type);
                    $voc->loadVocabulary();
                }
            break;
            case 'checkText':
                if(isset($_POST['text']) && $this->getType()){
                    $voc = new Vocabulary($this->type);
                    $voc->checkText($_POST['text']);
                }
            break;
        }
    }
    
     /**
      * Получение и присвоение типа
      * 
      * @return boolean Результат выполнения операции
      */       

    private function getType(){
        if($_POST['type']){
            $this->type = $_POST['type'];
            return true;
        }
        return false;
    }
}

