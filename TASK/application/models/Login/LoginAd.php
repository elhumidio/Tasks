<?php
/**
 * Esta clase gestiona el login de la aplicación mediante la autentificación en BD Local.
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 2.0 - 03/12/2012
 *
 */

class Application_Model_Login_LoginAd extends Application_Model_Login_Abstract
{
	const __MODELO__ = 'Ad';
	
	
	/**
	 * Nos loguea en la aplicación
	 * @param array $userData
	 */
	public function Authenticate($userConnectData=array(),$modelosDeConexion=array())
	{
		$this->login($userConnectData);						// Realizamos el login.
		parent::setCodeError($this->__get('codeError'));	// Recogemos el código de error.
		parent::setMsj($this->__get('msj'));				// Recogemos los msj. de error.
	
		$codeError = parent::procesarError($userConnectData,__MODELO__);
	
		//if($codeError && $codeError>=0) return true;	// Si el código de error es 0 significa que la autentificación es correcta.
	
	
		return false;
	}
	
	
	/**
	 * Nos autologuea en la aplicación
	 * @param array $userData
	 * @param string $modelo
	 */
	public function login($userConnectData)
	{
// 		$options = Zend_Registry::get ( 'configuracion.ini' );

		$db = new Application_Model_DbTable_User();
		
		$datos = $db->checkExist($userConnectData['username']);
		
		if($datos)
		{
			// OBTENGO LOS PARAMETROS DE CONEXION AL ACTIVE DIRECTORY
			$del = new Application_Model_Delegations();
			$options = $del->getDelegationAppValues('Active Directory',$datos['country']);
			
			$adapter = new Zend_Auth_Adapter_Ldap ( $options, $userConnectData['username'], $userConnectData['password'] );
			
			$namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT . '_Dashboard';
			$auth = Zend_Auth::getInstance (); // Creamos la instancia de autenticación.
			$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
			$result = $auth->authenticate ( $adapter ); // Pasamos el resultado de la validación de credenciales en Active Directory (AD) a la instancia de autentificación.
			
			$messages = $result->getMessages(); // Obtenemos y mostramos los posibles mensajes.
			
			foreach ($messages as $i => $message)
				if ($i-- > 1) parent::logLdap($message);
				
			// ¿Las credenciales son válidas?
			if ($result->isValid ()) return parent::checkAccess($userConnectData,self::__MODELO__);
			else parent::setCodeError($result->getCode());
		}
		else
		{
			// Buscamos los datos del usuario.
			$userModel = new Application_Model_User();
			$usuarioM = $userModel->getUserAdProfile($userConnectData);
			
// 			echo '<pre>';
// 			print_r($usuarioM);
// 			die();
			
			if(is_array($usuarioM))
			{
				$usuario = array();
				$usuario['Emp_WIW_Ident'] = $usuarioM ['Emp_WIW_Ident'];
				$usuario['username'] = $userConnectData ['username'];
				$usuario['role'] = $usuarioM ['role'];
				$usuario['country'] = $usuarioM ['CC'];
				$usuario['OU_ID'] = $usuarioM ['OU_ID']; // Grupo ID
				$usuario['created_at'] = date('Y-m-d H:i:s');
					
				
				// OBTENGO LOS PARAMETROS DE CONEXION AL ACTIVE DIRECTORY
				$del = new Application_Model_Delegations();
				$options = $del->getDelegationAppValues('Active Directory',$usuario['country']);
				
				$ldap = new Application_Model_Login_Procesos_Ldap();
				
				$userLdapInfo = $ldap->getInfo ($usuario, $options);
				
				if(count($userLdapInfo)>0)
				{
					
					$usuario['ldap_location'] = $userLdapInfo [0]['distinguishedname'][0];
					
					// Guardamos los datos del usuario en la BD. si es correcto volvemos al login.
					if(($usuario['ID'] = $db->insert($usuario))==true) return $this->login($userConnectData);
					else
					{
						array_push ($this->msj,$this->translate->_("Fallo al registrar el usuario."));
						return false;
					}
				}
				else
				{
					array_push ($this->msj,$this->translate->_("Fallo al registrar el usuario. No se reciben datos desde AD."));
					return false;
				}
			}
			
			array_push ($this->msj,$this->translate->_("Credenciales incorrectas, intentelo de nuevo o contacte con su administrador de sistema."));
			return false;
		}
		
		
		return false;
	}
}