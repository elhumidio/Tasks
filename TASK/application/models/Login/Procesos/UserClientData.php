<?php

class Application_Model_Login_Procesos_UserClientData
{
	private $translate;
	
	public function UserClientData()
	{
		return $this;
	}
	
	
	/**
	 * Nos devuelve el navegador
	 * @return string
	 */
	public function getBrowser ()
	{
		$user_agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Desconocido';
		
		$navegadores = array(
				'Opera' => 'Opera',
				'Mozilla Firefox'=> '(Firebird)|(Firefox)',
				'Chrome'=>'Chrome',
				'Safari'=>'Safari',
				'Galeon' => 'Galeon',
				'Mozilla'=>'Gecko',
				'MyIE'=>'MyIE',
				'Lynx' => 'Lynx',
				'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
				'Konqueror'=>'Konqueror',
				'IE 7' => 'MSIE 7',
				'IE 5' => 'MSIE 5',
				'IE 6' => 'MSIE 6',
				'IE 8' => 'MSIE 8',
				'IE 9' => 'MSIE 9',
				'Internet Explorer' => '(MSIE)'
		);
		
		foreach($navegadores as $navegador=>$pattern)
		{
			if (strstr($user_agent,$pattern))
				return $navegador;
		}
		
		return 'Desconocido';
	}
	
	
	/**
	 * Nos dice el SO que utiliza el usuario
	 * @return string
	 */
	public function getOs ()
	{
		$user_agent = strtolower (isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Desconocido');
		
		if(strpos($user_agent, "windows"))
			return 'Windows';
		else if(strpos($user_agent, "linux"))
			return 'Linux';
		else
			return 'Desconocido';
	}
	
	
	
	/**
	 * Nos dice la IP del cliente
	 * @var bool $encode
	 * @return string
	 */
	public function getIP ($encode=false)
	{
		if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
			$ip = getenv ( "HTTP_CLIENT_IP" );
		else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
			$ip = getenv ( "REMOTE_ADDR" );
		else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
			$ip = $_SERVER ['REMOTE_ADDR'];
		else
			$ip = "Unknown";
		
		return ($encode)?ip2long($ip):$ip;
	}
	
}