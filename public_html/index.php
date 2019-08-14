<?php
date_default_timezone_set('Europe/Madrid');																							// Defino la zona horaria

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')); 						// Defino la ruta de la carpeta application
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production')); 	// Defino el entorno de trabajo
define('LIBRARY_PATH',APPLICATION_PATH . '/../../ZendFramework/ZendFramework-1.11.11-minimal/library'); 
define('MODELS_PATH',APPLICATION_PATH . '/../../ZendFramework/Models'); 															// Defino la ruta de la libreria de Zend
define('NAMESPACE_BASE','Zend_Auth_EasyA');																							// Namespace base para el Zend_Auth, el mismo en todas las app easya
define('NAMESPACE_APP',NAMESPACE_BASE.'_Task');																								// Namespace unico para la app execpto para EasyAutomation que tiene que estar en blanco
define('NAMESPACE_MEMBER','storage');																								// Nombre de la variable que almacena los datos

set_include_path(implode(PATH_SEPARATOR, array(realpath(LIBRARY_PATH), get_include_path(),)));										// Incluyo la libreria de Zend

require_once 'Zend/Application.php';
require_once MODELS_PATH."/includes.php";

$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . '/configuracion.ini');		 								// Obtengo el archivo de configuración de la aplicación
$application->bootstrap()->run(); 																									// Lanzo la aplicación