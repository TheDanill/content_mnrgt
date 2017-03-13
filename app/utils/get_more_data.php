<?php

	// error_reporting(E_ALL & ~E_WARNING & ~E_COMPILE_WARNING & ~E_CORE_WARNING & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT & ~E_USER_DEPRECATED & ~E_USER_NOTICE & ~E_USER_WARNING);
	error_reporting(E_ALL);
	define('_INITIALIZED', true);
	define('_DEBUG', true);
	define('_DIR', __DIR__ . DIRECTORY_SEPARATOR);
	define('_BASEDIR', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);

	include_once '../db/db.php';
	include_once '../config.inc.php';

	if ( $result = DB::get()->select( 'SELECT * FROM `users`' ) ) {
		var_dump($result);
	}

	$curl = new HTTP();

	$obj = $curl->get_json('https://api.vk.com/method/users.get?user_id=' . $this->auth_info['user_id'] . '&fields=first_name,last_name,country,city,domain,nickname,photo_200&lang=ru', [], '', false, 0);



?>