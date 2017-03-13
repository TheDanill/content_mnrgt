<?php

class Cache {

    public static function redis() {
	static $c = null;
	if ($c === null) {
	    $c = new Cache();
        }
	return $c;
    }
    
    private $redis,
            $operation = 0;
    
    public  $timer = true;

    public function __construct() {
         $this->redis = new Redis();
         $this->redis->pconnect($GLOBALS['config']['cache']['server'], $GLOBALS['config']['cache']['port']);
         $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }
    
    public function __call($name, $arguments) {
        $operation = ++$this->operation;
        if (class_exists('Timer') && $this->timer) {
            $arg = is_string($arguments[0]) ? $arguments[0] : gettype($arguments[0]);
            $timer = "Cache " . $operation . ": " . $name . " (" . $arg . ")";
            Timer::get()->start($timer);
        }
        $return = call_user_func_array([$this->redis, $name], $arguments);
        if (class_exists('Timer') && $this->timer) {
            Timer::get()->stop($timer);
        }
        return $return;
    }

}