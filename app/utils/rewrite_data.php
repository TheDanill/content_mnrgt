<?php

error_reporting(E_ALL);
define('_INITIALIZED', true);
define('_DEBUG', true);
define('_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('_BASEDIR', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);

include_once '../db/db.php';
include_once '../config.inc.php';
include_once './translit.php';

$translit = new Translit();

$citys = array();

if ($result = DB::get()->select('SELECT * FROM `citys`')) {
    foreach ($result as $index => $city) {
        $citys[$city->id] = $city->name;
    }
}

// if (( $handle = fopen("KZ.csv", "r")) !== FALSE) {

//    $i = 0;

//    while (!feof($handle)) {

//        $data = explode("\t", fgets($handle));

//        $city_alias = explode(",", $data[3]);

//        foreach ($city_alias as $city) {
//            $key = array_search($city, $citys);
//            if ($key) {
//                break;
//            }
//        }


//        if ($key != FALSE) {
//            if ($data[2] != $citys[$key]) {

//                if ( preg_match('/[а-я\,\.]/ui', $citys[$key]) ) {
                   
//                    DB::get()->query('UPDATE `citys` SET `name`= "' . $data[2] . '" WHERE `name` = "' . $citys[$key] . '";');

//                    $translate = array(
//                        'en_US' => $data[2],
//                        'ru_RU' => $citys[$key]
//                    );


//                    $result = DB::get()->select('SELECT `en_US` FROM `lang_ru_RU` WHERE `en_US` = "' . $data[2] . '"');

//                    if (sizeof($result) == 0) {

//                        $result = DB::get()->select('SELECT `en_US` FROM `lang_ru_RU` WHERE `ru_RU` = "' . $citys[$key] . '"');

//                        if (sizeof($result) > 0) {

//                            $result = DB::get()->select('SELECT `en_US` FROM `lang_ru_RU_additional` WHERE `en_US` = "' . $data[2] . '"');

//                            if (sizeof($result) == 0) {

//                                DB::get()->insert('lang_ru_RU_additional', $translate);
//                            }
//                        } else {

//                            DB::get()->insert('lang_ru_RU', $translate);
//                        }
//                    }
//                }
//            }
//        }
//    }

//    fclose($handle);
// }


// Проверим города и добавим им латинские значения

foreach ($result as $index => $city) {
    if ( preg_match('/[а-я\,\.]/ui', $city->name) ) {
                  $translate = array(
                        'en_US' => $translit->cyrToLat( $city->name ),
                        'ru_RU' => $city->name
                    );
         DB::get()->query('UPDATE `citys` SET `name`= "' . $translit->cyrToLat( $city->name ) . '" WHERE `name` = "' . $city->name . '";');;
    }
}