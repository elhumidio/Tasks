<?php
/**
 * @author 		Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 	2.2 - 17/12/2012
 * @desc        Carga el idioma de la aplicación, si no existe la variable de sesión
 * 				busca el idioma preferido del navegador, verifica si está disponible 
 * 				y lo carga.
 */
class Application_Plugin_LangSelector extends Zend_Controller_Plugin_Abstract
{

	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	// Get our translate object from registry.
    	$registry = Zend_Registry::getInstance();
    	$translate = $registry->get('Zend_Translate');
    	
    	// Si recibimos el parametro lang es que estamos cambiando el idioma
    	$lang = $request->getParam('changeLang');
    	$session = new Zend_Session_Namespace('session');
    	
    	if($lang) $session->lang = $lang;
    	else $lang = self::findLang($session);
 
        $newLocale = new Zend_Locale($lang);
       	$registry->set('Zend_Locale', $newLocale);
       	
        $translate->setLocale($lang);
        $registry->set('Zend_Translate', $translate);
        
        //echo $translate->getLocale();
    }
    
    
    
    /**
     * Encuentra y devuelve el idioma de la aplicación. Si no existe el valor
     * en la sesión, busca el idioma preferente del navegador, verifica si está
     * disponible en el portal y lo carga por defecto.
     */
    private function findLang($session)
    {
    	$baseLang = 'en';
    	// Create Session block and save the locale
    	
    	
    	if(!isset($session->lang))
	    {
	    	// Obtenemos la configuración del navegador
	        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $accepted_languages = preg_split('/[,;]\s*/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			
			//print_r($accepted_languages);     
	        
	        if(isset($accepted_languages) && is_array($accepted_languages) && isset($accepted_languages[0]))
	        {
	        	$lang = substr($accepted_languages[0],0,2);
	
				// Get our translate object from registry.
		        $registry = Zend_Registry::getInstance();
		        $translate = $registry->get('Zend_Translate');
		        
		        if($translate->isAvailable($lang))
		        {
		        	$baseLang = $lang;
		        	$session->lang = $lang;
		        }
		        
	        }
    	}
    	else
    	{
    		$baseLang = $session->lang;
    	}
        
        return $baseLang;	// Nos devuelve las 2 letras del idioms: es;
    }
  
}