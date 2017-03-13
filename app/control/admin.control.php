<?php
abstract class AdminControl extends PageControl {
    
    use RequestControl;
    
    protected   $id;
    
    public function __construct() {
        parent::__construct();
        $this->getID();
    }

    protected function getID() {
        return $this->id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : false;
    }
    
}