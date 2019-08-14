<?php
/**
 * Este plugin carga el layout predefinido de cada módulo
 * 
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 *
 */
class Application_Plugin_Module extends Zend_Controller_Plugin_Abstract
{
	const MODULE_DIRECTORY_NAME = 'modulos';
	const LAYOUT_FOLDER_NAME	= 'plantillas';
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{

		$module = $request->getModuleName();
		$layout = Zend_Layout::getMvcInstance();
		
		$directorio = $layout->getLayoutPath();
		
		if($module!='default') // Si es distinto a default es que estamos en un módulo
			$directorio = APPLICATION_PATH.DIRECTORY_SEPARATOR.self::MODULE_DIRECTORY_NAME.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.self::LAYOUT_FOLDER_NAME.DIRECTORY_SEPARATOR;
		
		$layout->setLayoutPath($directorio);
		$layout->setLayout($layout->getLayout());
	}
	
	
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->init();
	
		// set up variables that the view may want to know
		$viewRenderer->view->moduleName = $request->getModuleName();
		$viewRenderer->view->controllerName = $request->getControllerName();
		$viewRenderer->view->actionName = $request->getActionName();
	}
}