<?php
error_reporting(E_ALL & ~E_WARNING & ~E_COMPILE_WARNING & ~E_CORE_WARNING & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT & ~E_USER_DEPRECATED & ~E_USER_NOTICE & ~E_USER_WARNING);
//error_reporting(E_ALL);
// error_reporting(0);
define('_INITIALIZED', true);
define('_DEBUG', true);
define('_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('_BASEDIR', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
define('_DIR_IMAGES', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR);

require __DIR__ . '/vendor/autoload.php';

include_once 'utils/timer.php';
$timer = Timer::get();
$timer->start('Includes');
include_once 'utils/autoload.php';
include_once 'utils/lang.php';
include_once 'db/db.php';
include_once 'db/cache.php';
include_once 'config.inc.php';
$timer->stop('Includes');
new MainControl();
/*if (_DEBUG && View::get()->mode === 'html') {
    echo '<!---' . PHP_EOL;
    $timer->dump();
    echo PHP_EOL . 'Used memory: ' . memory_get_peak_usage() . ' bytes' . PHP_EOL;
    //print_r(Cache::get()->getStats());
    echo '--->' . PHP_EOL;
}*/