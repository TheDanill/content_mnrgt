<?php

class Lang {

    public static function get() {
        static $lang;
        if ($lang === null) {
            $lang = new Lang();
        }
        return $lang;
    }

    public  $lang = 'en_US',
            $langs = [
                'ru' => 'ru_RU',
                'en' => 'en_US',
                'tt' => 'tt_TT'
                    ],
            $pairs = [];

    private function __construct() {
        $this->getLang();
        $this->setLang();
        if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] != $this->lang) {
            $this->saveLang();
        }
    }

    private function getLang() {
        try {
            $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            foreach ($this->langs as $index => $language) {
                if ( strpos( $accept_language, $index ) !== false ) {
                    $this->lang = $language;
                    $this->setLang();
                    break;
                }
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError( 'incompatible input', $e->getMessage() , 'File: ' . $e->getFile() . "\n" . 'Line: ' . $e->getLine() );
        }
    }

    private function setLang() {
        if ($this->lang != 'en_US') {
            $result = DB::get()->select('SELECT * FROM `lang_' . $this->lang . '`', DB::COLLECT);
            foreach ($result as $pair) {
                $this->pairs[$pair[0]] = $pair[1];
            }
        }
    }

    public function translate() {
        $args = func_get_args();

        if ($this->lang != 'en_US') {
            if (isset($this->pairs[$args[0]])) {
                $args[0] = $this->pairs[$args[0]];
            } else {
                return $args[0];
            }
            if (count($args) > 1) {
                $args[0] = call_user_func_array('sprintf', $args);
            }
        }
        return $args[0];
    }

    public function saveLang() {
        setcookie('lang', $this->lang, time() + 3650 * 86400, '/');
    }
    
    public function getLangById( $language_id ) {
        if ( $result = DB::get()->select('SELECT `code` FROM `languages` WHERE `id`=' . $language_id . ';') ) {
            return $result[0]->code;
        } else {
            return false;
        }
    }
    
    public function getIdByLang( $code = 'en' ) {
        if ($code !== false) {
            if ($result = DB::get()->select('SELECT `id` FROM `languages` WHERE `code`= "' . $code . '";')) {
                return $result[0]->id;
            }
        }
        return false;
    }
    
    public function getAllLangs() {
        return DB::get()->select("SELECT `id`, `name` FROM `languages`", DB::OBJECT, false, 86400);
    }

}

function __() {
    return call_user_func_array([Lang::get(), 'translate'], func_get_args());
}

function _A() {
    return Filter::mb_first(call_user_func_array('__', func_get_args()));
}

function _e() {
    echo call_user_func_array('__', func_get_args());
}
