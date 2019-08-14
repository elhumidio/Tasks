<?php
/**
 * Esta clase gestiona el login de la aplicación mediante la autentificación
 * en Local, Active Directory y WiW
 * Los sistemas de autentificación no son compatibles, por lo que hay que prestar
 * especial atención a que sistema se utiliza en cada aplicación.
 * 
 * 
 * @tutorial
 * 
 * Se puede decidir que módulos utilizar y en que orden. Para hacerlo hay que pasar un segundo valor array con los módulos
 * deseados y en el orden que decidamos.
 * 
 * if ($login->Authenticate($form->getValues(),array('Local','Ad','Wiw')))
 *		$this->_redirect ($login->__get('baseuri'));
 * 
 * @uses
 * 
 * Portales y su sistema de autentificación
 * 
 * * Easy.Automation:	Local + Active Directory + WiW.
 * * Easy.A Forms:		Active Directory.
 * * Easy.A Passwords:	Local + Active Directory + WiW.
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 3.0 - 30/11/2012
 *
 */

class Application_Model_Login_Authenticate extends Application_Model_Login_Abstract
{
	
	/**
	 * Inicia el módulo de autentificación.
	 * 
	 * Esta función carga los módulos necesarios para la autentificación en la aplicación.
	 * 
	 * Se pueden configurar qué módulos se quieren utilizar y el orden que se seguirá para la autentificación.
	 * 
	 * @param array $userData
	 * @param array[Local|Wiw|Ad] $modelosDeConexion
	 */
	public function Authenticate($userConnectData=array(),$modelosDeConexion=array('Local','Ad'))
	{
		die("1");
		parent::logout();
		
		$conexion= false;
		
		if(count($userConnectData)!=0)
		{
			foreach($modelosDeConexion as $modelo)
			{
				switch($modelo)
				{
					case 'Local':
						
						parent::logApp($this->translate->_('Motor de autentificación Local'));
						
						$loginLocal = new Application_Model_Login_LoginLocal(); // Motor.
						$conexion = $loginLocal->login($userConnectData);		// Realizamos el login.
						parent::setCodeError($loginLocal->__get('codeError'));	// Recogemos el código de error.
						parent::setMsj($loginLocal->__get('msj'));				// Recogemos los msj. de error.
						
						Break;
						
// 					case 'Wiw':
						
// 						parent::logApp($this->translate->_('Motor de autentificación WiW'));
						
// 						$loginWiW = new Application_Model_Login_LoginWiW();		// Motor.
// 						$conexion = $loginWiW->login($userConnectData);			// Realizamos el login.
// 						parent::setCodeError($loginWiW->__get('codeError'));	// Recogemos el código de error.
// 						parent::setMsj($loginWiW->__get('msj'));				// Recogemos los msj. de error.
						
// 						Break;
						
					case 'Ad':
						
						parent::logApp($this->translate->_('Motor de autentificación Ad'));
						
						$loginAd = new Application_Model_Login_LoginAd();		// Motor.
						$conexion = $loginAd->login($userConnectData);			// Realizamos el login.
						parent::clearCodeError();
						parent::setCodeError($loginAd->__get('codeError'));		// Recogemos el código de error.
						parent::clearMsjError();
						parent::setMsj($loginAd->__get('msj'));					// Recogemos los msj. de error.
						
						Break;
						
					default: 
						
						parent::setCodeError('-5');								// Recogemos el código de error.
						parent::setMsj(sprintf($this->translate->_('<b>ERROR:</b> El modelo <b>%s</b> no es un modelo de conexión válido.'),$modelo)); 
						parent::logApp(sprintf($this->translate->_('ERROR: El modelo %s no es un modelo de conexión válido.'),$modelo));
						return false;
						Break;
				}
				
				$codeError = $this->procesarError($userConnectData,$modelo);
				
				
				// Autentificación para aplicaciones externas
				if(isset($userConnectData['referer']) && $userConnectData['referer']!='' && $userConnectData['referer']!='/')
				{
					$this->baseuri = $userConnectData['referer'].'?sid='.Zend_Session::getId();
				}
				
				// Autentificación para aplicaciones Easy.A
				if(isset($userConnectData['easyReferer']) && $userConnectData['easyReferer']!='' && $userConnectData['easyReferer']!='/')
				{
					$this->baseuri = $userConnectData['easyReferer'].'/sso/'.Zend_Session::getId();
				}
				
				
				if($codeError==0) return true;	// Si el código de error es 0 significa que la autentificación es correcta.
				//if($codeError==-3) Break;		// Rompo el foreach por que la contraseña es incorrecta, lo que significa que la validación se realiza por este medio.
				if($codeError==-9) Break;		// Rompo el foreach por que la app está cerrada
			}
			
		}
		else 
		{
			// No se reciben los datos del usuario
			parent::setMsj($this->translate->_('<b>ERROR:</b> No se reciben los datos del usuario ($userData).'));
		}
				
		return false;
	}
	

	
}