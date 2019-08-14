<?php
/**
 * Esta clase gestiona el login de la aplicación mediante la autentificación en BD Local.
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 2.0 - 03/12/2012
 *
 */

class Application_Model_Login_LoginLocal extends Application_Model_Login_Abstract
{
	const __MODELO__ = 'Local';
	
	/**
	 * Nos loguea en la aplicación
	 * @param array $userData
	 */
	public function Authenticate($userConnectData=array(),$modelosDeConexion=array())
	{
		$this->login($userConnectData);						// Realizamos el login.
		parent::setCodeError($this->__get('codeError'));	// Recogemos el código de error.
		parent::setMsj($this->__get('msj'));				// Recogemos los msj. de error.
		
		$codeError = parent::procesarError($userConnectData,self::__MODELO__);
		
		//if($codeError && $codeError>=0) return true;	// Si el código de error es 0 significa que la autentificación es correcta.
		
		
		return false;
	}
	
	
	/**
	 * Proceso de login
	 * 
	 * @param array $userData
	 */
	public function login($userConnectData)
	{
		$userModel = new Application_Model_User();
		
		if($userModel->checkUserIsLocal($userConnectData['username']))
		{
			$user = new Application_Model_DbTable_User(); // Iniciamos la conexión con nuestro MySQL
			
			// Validamos los datos del usuario
			$authAdapter = new Zend_Auth_Adapter_DbTable ($user->getAdapter(),'user');
			$authAdapter->setIdentityColumn ('username')->setCredentialColumn ('password');
			$authAdapter->setIdentity ($userConnectData['username'])->setCredential($userConnectData['password']);
			$authAdapter->setCredentialTreatment ("MD5(?) AND ldap_location IS NULL");
				
			// Creamos la instancia de autenticación.
			$auth = Zend_Auth::getInstance ();
			$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
			
			// Pasamos el resultado de la validación de credenciales a la instancia de autentificación.
			$result = $auth->authenticate ( $authAdapter );
					
			
			// ¿Las credenciales son válidas?
			if ($result->isValid ()) return parent::checkAccess($userConnectData,self::__MODELO__);
			else parent::setCodeError($result->getCode());
		}
		else parent::setCodeError(-1);
		
		return false;
	}
}