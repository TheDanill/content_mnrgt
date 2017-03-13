<?php
class Pagination {
    static function gen($count, $page, $per_page, $around = 5, $d = "", $p = "", $active = "active", $dots = "dots", $last = false){
        if ($count <= $per_page)
            return false;
        $pages = ceil($count / $per_page);
        $pages_str = "<li" . ($page <= 1 ? " class=\"disabled\"" : "") . "><a" . ($page > 1 ? " href=\"". $d . ($page - 1) . $p . "\"" : "") . ">&lsaquo;</a></li>";
        if ($pages < $around * 2) {
                for ($i = 1; $i <= $pages; $i++) {
                    if ($i == $page){
                        $pages_str .= "<li class=\"" . $active . "\">";
                    }
                    else {
                        $pages_str .= "<li>";
                    }
                    $pages_str .= "<a href=\"". $d . $i . $p . "\">" . $i . "</a></li>";
                }
        }
        else {
                if ($page == 1) {
                    $pages_str .= "<li class=\"" . $active . "\">";
                }
                else { 
                    $pages_str .= "<li>";
                } 
                $pages_str .= "<a href=\"" . $d . "1" . $p . "\">1</a></li>";
                if ($page - $around > 1) {
                        $pages_str .= "<li class=\"" . $dots . "\"><a>...</a></li>";
                }
                for ($i = $page - $around + 1; $i < $page + $around; $i++) {
                        if ($i > 1 && $i <= $pages-1){
                                if ($i == $page) { 
                                    $pages_str .= "<li class=\"" . $active . "\">";
                                } 
                                else { 
                                    $pages_str .= "<li>";
                                } 
                                $pages_str .= "<a href=\"" . $d . $i . $p . "\">" . $i . "</a></li>";
                        }
                }
                if ($last || $page == $pages) {
                    if ($page + $around + 2 < $pages){
                            $pages_str .= "<li class=\"" . $dots . "\"><a>...</a></li>";
                    }
                    if ($page == $pages){
                            $pages_str .= "<li class=\"" . $active . "\">";
                    }
                    else{
                            $pages_str .= "<li>";
                    }
                    $pages_str .= "<a href=\"" . $d . $pages . $p . "\">" . $pages . "</a></li>";
                }
        }
        $pages_str .= "<li" . ($page == $pages && $count < 1000 && $count % 1000 != 0 ? " class=\"disabled\"" : "") . "><a" . ($page != $pages || ($count > 1000 && $count % 1000 == 0) ? " href=\"". $d . ($page + 1) . $p . "\"" : "") . ">&rsaquo;</a></li>";
        return $pages_str;
}

}