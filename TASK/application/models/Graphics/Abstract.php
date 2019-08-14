<?php
/**
 * Esta clase contienen los objetos que generan los gráficos de estadísticas
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 3.0 - 180/01/2013
 *
 */

abstract class Application_Model_Graphics_Abstract 
{
	static $urlBase;
	
	public function __construct()
	{
		self::setUrlBase();
	}
	
	/**
	 * Obtiene y guarda la url base de la aplicación
	 */
	public static function setUrlBase()
	{
		switch(APPLICATION_ENV)
		{
			case 'local': self::$urlBase = 'easya.loc'; break;
			case 'development': self::$urlBase = 'easyades'; break;
			default: self::$urlBase = 'easya.t-systems.es'; break;
		}
	}

}