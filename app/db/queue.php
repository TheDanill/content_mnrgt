<?php

class Queue {

    public static function q() {
	static $q = null;
	if ($q === null) {
	    $q = new Queue();
        }
	return $q;
    }
    
    private $memcache,
            $operation = 0;
    
    public function __construct() {
        //$this->memcache = new Memcache();
        //$this->memcache->pconnect($GLOBALS['config']['queue']['server'], $GLOBALS['config']['queue']['port']);
    }
    
    public function get($name) {
        if (class_exists('Timer')) {
            $operation = ++$this->operation;
            $timer = "Queue " . $operation . ": get " . $name;
            Timer::get()->start($timer);
        }
        $return = null;
        //if ($value = $this->memcache->get($name)) {
        //    $return = igbinary_unserialize($value);
        //}
        if (class_exists('Timer')) {
            Timer::get()->stop($timer);
        }
        return $return;
    }
    
    public function set($name, $value) {
        if (class_exists('Timer')) {
            $operation = ++$this->operation;
            $timer = "Queue " . $operation . ": set " . $name;
            Timer::get()->start($timer);
        }
        //$return = $this->memcache->set($name, igbinary_serialize($value));
        if (class_exists('Timer')) {
            Timer::get()->stop($timer);
        }
        //return $return;
    }

}