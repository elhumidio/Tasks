<?php

class Checklist_IndexController extends Zend_Controller_Action
{
	
    public function init()
    {
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/fullcalendar.css','screen' );
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/fullcalendar.print.css','print' );
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/jquery.kwicks.css','screen' );
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/checklist.css','screen,print' );
    	$this->view->headLink()->appendStylesheet ( '/estilos/css/animate.css','screen,print' );
    	$this->view->headLink()->appendStylesheet ( '/estilos/css/jquery.tagit.css','screen,print' );
    	
    	$this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery/plugin/fullcalendar.js' );
    	$this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery.kwicks.js' );
    	$this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/checklist.js' );
    	$this->view->headScript()->appendFile ( '/estilos/js/lettering.js' );

    	$this->view->headScript()->appendFile ( '/estilos/js/fittext.js' );
    	//$this->view->headScript()->appendFile ( '/estilos/js/moment.min.js' );
    	$this->view->headScript()->appendFile ( '/estilos/js/tag-it.min.js' );
    	$this->view->headScript()->appendFile ( '/estilos/js/gistfile1.js' );    	
    }

    public function indexAction()
    {

    }
    
}

