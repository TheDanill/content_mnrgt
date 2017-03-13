<?php

class ErrorControl {
    
    public static function get() {
        static $error;
        if ($error === null) {
            $error = new ErrorControl();
        } 
        return $error;
    }
    
    public function __construct() {}
    
    public function writeError( $type = '', $description = '', $where = '') {
        
        $data['type'] = $type;
        $data['description'] = $description;
        $data['where'] = $where;
        
        ErrorModel::get()->insert($data);
        
    }
    
}

