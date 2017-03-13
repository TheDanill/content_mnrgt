<?php

function autoload($name) {
    if (preg_match('/^(.*)(Control|Model)$/i', $name, $matches))
	include_once _DIR . strtolower($matches[2]) . '/' . strtolower($matches[1]) . '.' . strtolower($matches[2]) . '.php';
    elseif ($name == 'View')
	include_once _DIR . 'view/view.php';
    elseif ($name == 'Filter')
        include_once _DIR . 'utils/filter.php';
    elseif ($name == 'Queue')
        include_once _DIR . 'db/queue.php';
    elseif ($name == 'Pagination')
        include_once _DIR . 'utils/pagination.php';
    elseif ($name == 'HTTP')
        include_once _DIR . 'utils/http.php';
    elseif ($name == 'Translit')
        include_once _DIR . 'utils/translit.php';
    elseif ($name == 'Vocabulary')
        include_once _DIR . 'utils/vocabulary.php';
    elseif ($name == 'Image')
        include_once _DIR . 'utils/image.php';
}

spl_autoload_register('autoload');