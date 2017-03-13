<?php
//Контроллер утилиты перевода (не используется)
class LangControl extends PageControl {
    
    protected $actions = [
        'translate'
    ];
    
    public function __construct() {
        parent::__construct();
        print_r($_POST);
        switch($this->action) {
            case 'translate':
                
            break;
        }
    }
}

