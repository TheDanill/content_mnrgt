<?php

class Filter {

    public static function remove(&$a, $filter, $timer = true) {
        static $n = 0;
        if ($timer)
            Timer::get()->start('Removing keys from array ' . ++$n);
        foreach (array_keys($a) as $k) {
            if (isset($filter[$k]) && is_array($filter[$k]))
                self::remove($a[$k], $filter[$k], false);
            elseif (in_array($k, $filter))
                unset($a[$k]);
        }
        unset($k);
        if ($timer)
            Timer::get()->stop();
    }
    
    public static function mb_trim($string, $chars = "", $chars_array = array()) {
        mb_internal_encoding('utf-8');
        for ($x=0; $x<iconv_strlen($chars ); $x++)
            $chars_array[] = preg_quote(iconv_substr($chars, $x, 1));
        $encoded_char_list = implode("|", array_merge( array("\s","\t","\n","\r", "\0", "\x0B"), $chars_array));

        $string = mb_ereg_replace("^($encoded_char_list)*", "", $string);
        $string = mb_ereg_replace("($encoded_char_list)*$", "", $string);
        return $string;
    }
    
    public static function mb_cut($string, $l = 100, $end = '&hellip;') {
        mb_internal_encoding('utf-8');
        if (mb_strlen($string) > $l) {
            $string = mb_strcut($string, 0, mb_strrpos(' ', $string, $l));
            $string = mb_ereg_replace('[^\w\d]+$', $end, $string);
        }
        return $string;
    }
    
    public static function mb_first($string) {
        mb_internal_encoding('utf-8');
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1, mb_strlen($string) - 1);
    }

    public static function toYears($time) {
        return floor((time()-(int)$time)/31536000);
    }
    
    public static function phone($n) {
        return preg_replace('/[\-\s\(\)]/', '', $n);
    }
    
    public static function date($date) {
        if (!($ret = strtotime($date))) {
            $date = preg_replace(self::$datePatterns, array_keys(self::$datePatterns), $date);
            $date = preg_replace('/[^\d]+/', '.', $date);
            if (preg_match('/^\d{1,2}\.\d{1,2}$/', $date))
                    $date .= '.' . date('Y');
            $ret = strtotime($date);
        }
        return intval($ret);
    }
    
    public static function timeToDate($time) {
        $y = date('Y', $time);
        $m = date('F', $time);
        $d = date('j', $time);
        return $d . ' ' . Lang::_($m) . (date('Y') == $y ? '' : ' ' . $y);
    }

    public static function money($price) {
        $price = preg_replace('[\.,]', '.', $price);
        preg_match('/^(\d+)(\.\d+)?(\.|$)/u', $price, $nums);
        if ($nums[2])
            $price = round(floatval($nums[1] . $nums[2]), 2);
        else
            $price = intval($nums[1]);
        return $price;
    }
    
    public static function clearStr($str) {
        return mb_ereg_replace('\s+', ' ', mb_ereg_replace('[^\w\s\d]', '', $str));
    }
    
    public static function secToTime($sec) {
        if ($sec < 0)
            return '00:00:00';
        elseif ($sec < 3600)
            return date('i:s', $sec);
        else
            return ($sec < 36000 ? '0' : '') . floor($sec / 3600) . date(':i:s', $sec % 3600);
    }
    
    public static function secToTimeArray($sec) {
        if (!is_int($sec)) {
            if (preg_match('/^(\d+):(\d+):(\d+)$/', $sec, $time)) {
                $sec = $time[1] * 3600 + $time[2] * 60 + $time[3];
            }
            else {
                $sec = (int)$sec;
            }
        }
        if ($sec < 0)
            return ['h' => 0, 'm' => 0, 's' => 0];
        elseif ($sec < 3600)
            return ['h' => 0, 'm' => date('i', $sec), 's' => date('s', $sec)];
        else
            return ['h' => floor($sec / 3600), 'm' => date('i', $sec % 3600), 's' => date('s', $sec % 3600)];
    }
    
    public static function toDateFormat($date, $pattern = 'd.m.Y H:i:s') {
        return date($pattern, strtotime($date));
    }
    
    public static function timeDiff($date1, $date2 = 'now') {
        return abs(strtotime($date1) - strtotime($date2));
    }
    
    public static function replaceFloats(&$a) {
        foreach ($a as &$b) {
            if (is_array($b))
                self::replaceFloats($b);
            else
                $b = self::replaceFloat($b);
        }
    }
    
    public static function replaceFloat($a) {
        if (!is_numeric($a) && is_string($a) && preg_match('/^\d+,\d+$/', Filter::mb_trim($a))) {
            return str_replace(',', '.', Filter::mb_trim($a));
        }
        else
            return $a;
    }
    
    public static function toInt($a) {
        if (is_array($a) || is_object($a)) {
            foreach ($a as &$b) {
                $b = self::toInt($b);
            }
        }
        else {
            $a = intval($a);
        }
        return $a;
    }
    
    public static function toFloat($a) {
        if (is_array($a) || is_object($a)) {
            foreach ($a as &$b) {
                $b = self::toFloat($b);
            }
        }
        else {
            $a = floatval(self::replaceFloat($a));
        }
        return $a;
    }
    
    public static function toObject($a) {
        $b = new stdClass;
        foreach ($a as $k => $v) {
            $b->$k = $v;
        }
        return $b;
    }
    
    public static function sanitizeHTML($str) {
        if (is_numeric($str))
            return $str;
        elseif (is_array($str) || is_object($str)) {
            foreach ($str as &$val) {
                $val = self::sanitizeHTML($val);
            } unset($val);
            return $str;
        }
        elseif (is_string($str)) {
            $dom = new DOMDocument();
            $dom->resolveExternals = false;
            $dom->strictErrorChecking = false;
            $dom->loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title></title></head><body>' . $str . '</body></html>');
            $dom->normalize();
            $all = $dom->getElementsByTagName('body')->item(0)->childNodes;
            for ($i = 0; $i < $all->length; $i++)
                self::sanitizeNode($all->item($i));
            $imgs = $dom->getElementsByTagName('img');
            for ($i = 0; $i < $imgs->length; $i++)
                self::sanitizeNode($imgs->item($i));
            return preg_replace('/(^<body>|<\/body>$)/i', '', $dom->saveXML($dom->getElementsByTagName('body')->item(0)));
        }
        else
            return $str;
    }
    
    private static function sanitizeNode(DOMNode $node) {
        $bad_tags = array(
            'script',
            'link',
            'iframe',
            'frameset',
            'frame',
            'style',
            'bgsound',
            'embed',
            'meta',
            'object'
        );
        if (in_array(strtolower($node->nodeName), $bad_tags)) {
            $node->parentNode->removeChild($node);
            return;
        }
        $el = $node;
        for ($j = 0; $j < $el->attributes->length; $j++) {
            if (strpos($el->attributes->item($j)->nodeName, 'on') === 0)
                $el->attributes->item($j)->ownerElement->removeAttribute($el->attributes->item($j)->nodeName);
        }
        if ($el->hasChildNodes()) {
            $all = $el->childNodes;
            for ($i = 0; $i < $all->length; $i++)
                self::sanitizeNode($all->item($i));
        }
        $el->parentNode->replaceChild($el, $node);
    }
    
    public static function removeDuplicates($a, $dupArray = array(), $insetArray = '') {
        $f = array();
        foreach ($a as $k => $v) {
            $args = array();
            if ($insetArray) {
                foreach ($v[$insetArray] as $key => $val) {
                    foreach ($dupArray as $p) {
                        array_push($args, $val[$p]);
                    }
                    $arg = implode('||', $args);
                    if (isset($f[$arg])) {
                        unset($a[$k][$insetArray][$key]);
                        $a[$f[$arg][0]][$insetArray][$f[$arg][1]]['duplicates']++;
                    }
                    else {
                        $f[$arg] = array($k, $key);
                        $a[$k][$insetArray][$key]['duplicates'] = 1;
                    }
                    unset($key, $val);
                }
            }
            else {
                foreach ($dupArray as $p) {
                    array_push($args, $v[$p]);
                }
                $arg = implode('||', $args);
                if (isset($f[$arg])) {
                    unset($a[$k]);
                    $a[$f[$arg]]['duplicates']++;
                }
                else {
                    $f[$arg] = $k;
                    $a[$k]['duplicates'] = 1;
                }
            }
            unset($k, $v, $arg, $args, $p);
        }
        unset($f);
        return $a;
    }

}