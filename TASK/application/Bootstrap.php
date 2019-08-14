<?php

/**
 * Para configurar una nueva app hay que definir:
 * 
 * 1. $fileBaseName para la función _initSetupBaseUrl() en bootstrap.php
 * 2. RewriteBase en .htaccess
 * 3. NAMESPACE_APP en index.php
 *
 * @author jbadia
 *
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	/**
	 * El nombre de los archivos básicos de la aplicacion (xx.css, ie_xx.css, xx.js, etc)
	 * @var string
	 */
	private $fileBaseName = 'Task';
	
	/**
	 * URL BASE del proyecto. IMPORTANTE
	 * @var string
	 */
	private $urlBase = '/task';
	
	/**
	 * Versión de la aplicación. 
	 * @var int
	 */
	private $version = 0.2;
	
	/**
	 * Fecha de creación de la app
	 * @var string
	 */
	private $fechaApp = '22/07/2013';
	
	/**
	 * Archivo config.ini. Root en Application.
	 * @var string
	 */
	private $configurationFileName = 'configuracion.ini';
	
	
	
	/**
	 * Iniciamos la sesion del usuario
	 */
/*	protected function __initSession()
	{
		Zend_Session::start();
	}*/
	
	
	/**
	 * Esta función carga todo el archivo application.ini en el registro
	 * para poder usarla más adelante.
	 */
	protected function _initConfig()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/'.$this->configurationFileName, APPLICATION_ENV );
		$options = $config->toArray();
		Zend_Registry::set ( $this->configurationFileName, $options );
	}
	
	
	
	/**
	 * Especifica la BAse URL para poder funcionar un un subdirectorio
	 */
	protected function _initSetupBaseUrl()
	{
		$this->bootstrap('frontcontroller');
		$controller = Zend_Controller_Front::getInstance();
		$controller->setBaseUrl($this->urlBase);
		Zend_Registry::set('urlBase', $this->urlBase);
	}
	
	protected function initPartialLoopObject()
	{
		$this->_view->partialLoop()->setObjectKey('object');
	
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->setView($this->_view);
	}
	/**
	 * Defino las diferentes bases de datos
	 */
	protected function _initDatabase ()
	{
		$this->bootstrap('multidb');
		$multidb = $this->getPluginResource('multidb');
		
		$options = Zend_Registry::get ( $this->configurationFileName );
		$db = Zend_Db::factory($options['resources']['db']['adapter'], $options['resources']['db']['params']);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		
		Zend_Registry::set('pentaho', $multidb->getDb('pentaho'));
		Zend_Registry::set('easya', $multidb->getDb('easya'));
		Zend_Registry::set('cmdb', $multidb->getDb('cmdb'));
	}
	
	
	
	/**
	 * Función que inicializa el cache general de la web
	 * Incluidos los metadatos de las consultas SQL (describe)
	 */
	public function _initDbCache()
	{
		$options = Zend_Registry::get ( $this->configurationFileName );
		$frontendOptions = array('lifetime' => $options['cache']['lifetime'],'automatic_serialization' => $options['cache']['automatic_serialization']);
		$backendOptions  = array('cache_dir' => $options['cache']['dir']);
		$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
		Zend_Registry::set ( 'Cache', $cache );
	}
	
	
	
	
	protected function _initViewHelpers()
	{
		$this->bootstrap ( 'view' );
		
		$view = $this->getResource ( 'view' );
		
		// View Helpers externos
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->initView();
		$viewRenderer->view->addHelperPath(MODELS_PATH.'/helpers');
		
		/**
		 * Setup the Custom Helpers
		 */

		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/views/helpers');
		// Título
		$view->headTitle ( 'easy.A' )->setSeparator ( ' | ' );
	
		$view->headMeta()->appendName('Version', $this->version);
		$view->headMeta()->appendName('Author', 'Automation and Tools, T-Systems Iberia');
		//$view->headMeta()->appendHttpEquiv('refresh','3600; url='.Zend_Controller_Front::getInstance()->getBaseUrl().'/auth/logout');
		
		
// 		$view->headLink()->headLink ( array ('type' => 'image/x-icon', 'rel' => 'shortcut icon', 'href' => Zend_Controller_Front::getInstance()->getBaseUrl().'/favicon.ico' ), 'PREPEND' );
// 		$view->headLink()->headLink ( array ('type' => 'image/x-icon', 'rel' => 'apple-touch-icon', 'href' => Zend_Controller_Front::getInstance()->getBaseUrl().'/favicon.ico' ), 'PREPEND' );
// 		$view->headLink()->headLink ( array ('type' => 'image/x-icon', 'rel' => 'apple-touch-icon', 'sizes'=>'72x72', 'href' => Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icon_ipad_72x72.png' ), 'PREPEND' );
// 		$view->headLink()->headLink ( array ('type' => 'image/x-icon', 'rel' => 'apple-touch-icon', 'sizes'=>'114x114', 'href' => Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icon_ipad_114x114.png' ), 'PREPEND' );
		
		
		$view->headLink()->appendStylesheet ( '/estilos/css/reset.css','screen,print' );
		$view->headLink()->appendStylesheet ( '/estilos/css/main.css','screen,print' );
		$view->headLink()->appendStylesheet ( '/estilos/css/notify.css' );
		$view->headLink()->appendStylesheet ( '/estilos/css/forms.css','screen,print' );
		$view->headLink()->appendStylesheet ( '/estilos/css/iphone-style-checkboxes.css' );
		$view->headLink()->appendStylesheet ( '/estilos/css/ui/customtheme/jquery-ui-1.8.21.custom.css','screen,print');

		$view->headLink()->appendStylesheet ( '/estilos/css/graph.css','screen,print');
// 		$view->headLink()->appendStylesheet ( '/estilos/css/tourist.css','screen,print');
		$view->headLink()->appendStylesheet ( '/estilos/css/icons/easy.icons.css','screen,print');
		$view->headLink()->appendStylesheet ( '/estilos/css/jquery.jeegoocontext.css','screen,print');
		$view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/'.$this->fileBaseName.'.css','screen,print');
        $view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/TableTools-2.2.4/css/dataTables.tableTools.css','screen,print');
        // $view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/jquery.dataTables.css','screen,print');
        // $view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/dataTables.tableTools.css','screen,print');        
        // $view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/jquery.dataTables_themeroller.css','screen,print');
            $view->headLink()->appendStylesheet ( '/estilos/DataTables-1.9.1/media/css/jquery.dataTables.css');
        //$view->headLink()->appendStylesheet ( '/estilos/DataTables-1.9.1/extras/TableTools-2.1.1/media/css/TableTools.css');
       
       
		// Hack IE
		$view->headLink ()->appendStylesheet ( '/estilos/css/_iehack.css', 'screen', 'lte IE 7' );
		$view->headLink ()->appendStylesheet ( '/estilos/css/icons/ie.easy.icons.css', 'screen', 'lte IE 8' );
		$view->headLink ()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/ie_'.$this->fileBaseName.'.css', 'screen', 'lte IE 8' );


		
		$view->headScript()->appendFile ( '/estilos/js/jquery/jquery-1.7.2.min.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/DataTables-1.10.7/jquery.dataTables.min.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/TableTools-2.2.4/js/dataTables.tableTools.min.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/Bitacora/dateFunctions.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/Bitacora/NavigateFunctions.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/Bitacora/MiscellanousFunctions.js' );
        $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/Bitacora/CISearch.js' );
          $view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/Export/Export.js' );
        $this->view->headLink()->appendStylesheet (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/buttons.dataTables.min.css');
         

         $this->view->headLink()->appendStylesheet (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/select2.min.css');

       
        $this->view->headScript()->appendFile (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery.dataTables.min.js');
        $this->view->headScript()->appendFile (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/dataTables.buttons.min.js');
        $this->view->headScript()->appendFile (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/buttons.html5.min.js');
        $this->view->headScript()->appendFile (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/buttons.print.min.js' );
        $this->view->headScript()->appendFile (Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/select2.min.js' );
        
        
        
		
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.notify.js' );
		
// 		$view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery/jquery-1.9.1.js' );
		$view->headScript()->appendFile ( '/estilos/js/base64.js' );
		
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.selectorIdioma.js' );
// 		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.tourist.js' );
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.tools.min.js' );
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.cookie.js' );
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.jeditable.js' );
		
// 		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.contextMenu.js' );
		$view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.jeegoocontext-2.0.0.min.js' );
		
		$view->headScript()->appendFile ( '/estilos/js/jquery/ui/jquery-ui-1.8.21.custom.min.js' );
		$view->headScript()->appendFile ( '/estilos/js/jquery/ui/plugins/jquery-ui-timepicker-addon.js' );

		$view->headScript()->appendFile ( '/estilos/js/cookies.js' );
		$view->headScript()->appendFile ( '/estilos/js/webtoolkit.md5.js' );
		$view->headScript()->appendFile ( '/estilos/js/EasyAutomation.js' );
		$view->headScript()->appendFile ( '/estilos/js/tag-it.min.js' );
		$view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/'.$this->fileBaseName.'.js' );		
	}
	
	
	
	protected function _initNavigation()
	{
		$this->bootstrap ( 'layout' );
		$layout = $this->getResource ( 'layout' );
		$view = $layout->getView ();
	
		$resource = $this->getPluginResource('db');
		$db = $resource->getDbAdapter();
	
		$m = new Application_Plugin_Navegacion();
	
		$config = new Zend_Config_Xml ( $m->run($db) );
		$navigation = new Zend_Navigation ( $config );
	
		$view->navigation ( $navigation );
	}
	
	
	
	
	
	
	protected function _initTranslate()
	{
		// somewhere in your application
		$translate = new Zend_Translate ( array ( 'adapter' => 'gettext', 'content' => APPLICATION_PATH . '/idiomas/es_ES.mo', 'locale' => 'es' ) );
		$translate->addTranslation ( array ( 'content' => APPLICATION_PATH . '/idiomas/ca_ES.mo', 'locale' => 'ca' ) );
		$translate->addTranslation ( array ( 'content' => APPLICATION_PATH . '/idiomas/en_EN.mo', 'locale' => 'en' ) );
		
	
		//Zend_Controller_Router_Route::setDefaultTranslator($translate);
		Zend_Registry::set ( 'Zend_Translate', $translate );
		Zend_Validate_Abstract::setDefaultTranslator ( $translate );
		Zend_Form::setDefaultTranslator ( $translate );
		$this->view->translate = $translate;
	}
	
	
	/**
	 * Esta función propaga la configuración de la aplicación a todo el portal
	 */
	protected function _initAppData()
	{
		$db = new Application_Model_DbTable_AppConfig();
		
		$this->view->app = new stdClass();
		$this->view->app->configuration = Zend_Registry::get ( $this->configurationFileName );
		$this->view->app->appConfig = $db->getAppConfig();
		$this->view->app->fileBaseName = $this->fileBaseName;
		$this->view->app->fechaApp = $this->fechaApp;
		$this->view->app->version = $this->version;
	}
	
}