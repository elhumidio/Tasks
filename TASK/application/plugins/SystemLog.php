<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_SystemLog
 * @filesource  application/plugins/SystemLog.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.SystemLog = "Application_Plugin_SystemLog"
 *              
 *              Uso:
 *              $l_app = new Application_Plugin_SystemLog();
 *              $l_app->setDb($db);
 *              $l_app->setLog('Accion'); // Nivel de error: INFO
 *              $l_app->setLog('Accion',5); // Nivel de Error Manual
 *              
 *              Niveles de aviso:
 *              EMERG   = 0;  // Emergency: system is unusable
 *			    ALERT   = 1;  // Alert: action must be taken immediately
 *			    CRIT    = 2;  // Critical: critical conditions
 *				ERR     = 3;  // Error: error conditions
 *				WARN    = 4;  // Warning: warning conditions
 *				NOTICE  = 5;  // Notice: normal but significant condition
 *				INFO    = 6;  // Informational: informational messages
 *				DEBUG   = 7;  // Debug: debug messages
 *              
 * @desc        Registra en la Base de datos las secciones que visita cada usuario.
 */
class Application_Plugin_SystemLog extends Zend_Controller_Plugin_Abstract
{
	protected $prefijo		= false;
    protected $l;
    protected $emailFrom	= 'easya.dashboard@t-systems.es';
    protected $emailTo		= 'juanignacio.badia@t-sistems.es';
    
	
    public function setLog($accion,$lvl='6',$extras=NULL)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$colAction = array('lvl' => 'priority', 'msj' => 'message',);
		$wriAction = new Zend_Log_Writer_Db($db, 'log_app', $colAction);
		$l = new Zend_Log($wriAction);
		//$l->log($accion, Zend_Log::INFO, $extras);
		
		if($this->prefijo)
			$accion = $this->prefijo.' ---> '.$accion;
		
		switch($lvl)
		{
			case '0':
				$l->emerg($accion, $extras);
				// Envio el error por email
				$this->emailLog($accion, $extras);
				break;
			case '1':
				$l->alert($accion, $extras);
				break;
			case '2':
				$l->crit($accion, $extras);
				break;
			case '3':
				$l->err($accion, $extras);
				break;
			case '4':
				$l->warn($accion, $extras);
				break;
			case '5':
				$l->notice($accion, $extras);
				break;
			case '6':
				$l->info($accion, $extras);
				break;
			case '7':
				$l->debug($accion, $extras);
				break;
			default:
				$l->info($accion, $extras);
				break;
		}
    	
    }
    
    public function setPrefijo($prefijo)
    {
    	$this->prefijo = $prefijo;
    }
    
	private function emailLog($accion, $extras)
    {
    	
		$mail = new Zend_Mail();
		$mail->setFrom($this->emailFrom)
			 ->addTo($this->emailTo);
			 
		$writer = new Zend_Log_Writer_Mail($mail);
		$writer->setSubjectPrependText('PRD: Error de nivel 0');
		$writer->addFilter(Zend_Log::WARN);
		
		$log = new Zend_Log();
		$log->addWriter($writer);
		$log->error($accion, $extras);
	}
    
}