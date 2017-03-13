<?php
/*
	URL Manager
	author: @foxovsky
*/

class urlManager
{
	public function getPage($url)
	{
		$page = curl_init();
		curl_setopt($page, CURLOPT_URL, $url);
		curl_setopt($page, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($page, CURLOPT_SSL_VERIFYPEER, false);
		curl_close($page);
		
		return $page;
	}
}
?>