<?php

class Image {
    
    static  $max_width = 5000,
            $max_height = 7000,
            $quality = 90;
    
    public static function check($f, $returnInfo = false) {
        $info = getimagesize($f);
        if ($info && in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG)) && $info[0]>0 && $info[1]>0 && $info[0]<=self::$max_width && $info[1]<=self::$max_height)
            if ($returnInfo)
                return $info;
            else
                return true;
        else
            return $info;
    }
    
    public static function save($f, $w, $h, $to = '', $delete = false) {
        if ($info = self::check($f, true)) {
            /*switch ($info[2]) {
                case IMAGETYPE_GIF:
                    //$img = imagecreatefromgif($f);
                    $ext = '.gif';
                    break;
                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    //$img = imagecreatefromjpeg($f);
                    $ext = '.jpg';
                    break;
                case IMAGETYPE_PNG:
                    //$img = imagecreatefrompng($f);
                    $ext = '.png';
                    break;
            }
            /*$k = ($info[0]>$w || $info[1]>$h) ? ($w/$info[0]<$h/$info[1] ? $w/$info[0] : $h/$info[1])  : 1;
            $w = intval($info[0]*$k);
            $h = intval($info[1]*$k);
            unset($k);
            $tmp = imagecreatetruecolor($w, $h);
            imagecopyresized($tmp, $img, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);*/
            if (empty($to))
                $to = md5(microtime());
            $path = $to;
            shell_exec("convert {$f} -background white -flatten -alpha off -thumbnail '{$w}x{$h}>' -quality " . self::$quality . "% {$path}");
            /*
            if ($ext == '.jpg')
                imagejpeg($tmp, _BASEDIR . 'images/' . $to . $ext, self::$quality);
            elseif ($ext == '.png')
                imagepng($tmp, _BASEDIR . 'images/' . $to . $ext);
            imagedestroy($img);
            imagedestroy($tmp);
            unset($info, $w, $h, $img, $f, $tmp);*/
            if ($delete)
                unlink($f);
            unset($f, $path, $w, $h);
            return $to;
        }
        else
            return false;
    }
    
}