<?php

class ErrorModel {
    
    public static function get() {
        static $error;
        if ($error === null) {
            $error = new ErrorModel();
        } 
        return $error;
    }
    
    public function __construct() {}
    
    public function insert( $data ) {
        DB::get()->insert( 'errors', $data );
    }
    
}