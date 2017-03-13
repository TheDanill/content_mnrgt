<?php

class PlacesModel {
    
    private $users = [];
    
    public function __construct() {
        ;
    }
    
    public function getCurrentPlaces($page = 1, $per_page = 100000) {
        $offset = ($page - 1) * $per_page;
        if ($this->users = DB::get()->select("SELECT `id`, `place` FROM `users` WHERE `status` = 'active' LIMIT " . $offset . ", " . $per_page)) {
            if (count($this->users)) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    public function updatePlaces() {
        $page = 1;
        while ($this->getCurrentPlaces($page)) {
            
            $page++;
        }
    }
    
}