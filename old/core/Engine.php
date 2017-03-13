<?php
/*
	Engine
	author: @foxovsky
*/
include("managers/urlManager.php");

class Engine
{
	global $url;
	
	public function __construct($url)
	{
		$this->url = $url;
	}
	
	/**
    * Version getter
    *
    * @return string
    */
	public function getVersion()
	{
		$urlManager = new urlManager();
		$page = $urlManager->getPage($url);
		
		return $version;
	}
	
	/**
    * Authors getter
    *
    * @return array
    */
	public function getAuthors()
	{
		for ($i = 1; $i <= 10; $i++)
		{
			$urlManager = new urlManager();
			$page = $urlManager->getPage($url ."?author=". $i);
			
			
		}
	}
}

?>