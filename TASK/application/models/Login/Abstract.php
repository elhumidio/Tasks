<?php
/**
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 2.0 - 03/12/2012
 *
 */

abstract class Application_Model_Login_Abstract  
{
	/**
     * Objeto de la conexión a la BD.
     * @var object: Zend_Db_Table_Abstract::getDefaultAdapter()
     */
    protected $db;
    
    
    /**
     * Objeto de la conexión a la BD pentaho
     * @var object: Zend_Registry::get('pentaho')
     */
    protected $pentaho;
    
    
    /**
     * 
     * Variable que contiene la informacion del usuario.
     * @var array
     */
    protected $user_data;
    
	
	/**
	 * Variable que contiene el objeto Translate
	 * @var object: Zend_Registry::get ( 'Zend_Translate' )
	 */
	protected $translate;
	
	
	/**
	 * Objeto Log LDAP
	 * @var object: Application_Plugin_LdapLog()
	 */
	protected $ldapLog;
	
	
	/**
	 * Objeto Log Access Usuario
	 * @var object: Application_Plugin_UserLogAccess()
	 */
	protected $userLogAccess;
	
	
	/**
	 * Objeto Log Usuario
	 * @var object: Application_Plugin_UserLogAccess()
	 */
	protected $userlog;
	
	
	/**
	 * Objeto Log de Sistema
	 * @var object: Application_Plugin_SystemLog()
	 */
	protected $systemLog;
	
	
	/**
	 * Objeto que obtiene los datos del cliente del usuario
	 * @var object: Application_Model_Login_UserClientData()
	 */
	protected $userClientData;
	
	
	
	/**
	 * Variable que almacena los msj del modelo
	 * @var array
	 */
	protected $msj=array();
	
	
	/**
	 * Variable que almacena la Base URL para la redirección
	 * @var unknown_type
	 */
	protected $baseuri = '/';
	
	
	/**
	 * Variable que almacena el codigo de Error devuelto por el módulo de autentificación
	 * @var string
	 */
	protected $codeError = 0;
	
	
	
	
	
	/**
	 * Función que inicializa todos los procesos necesarios
	 * en las clases que heredan de esta
	 * 
	 * @param array $options
	 */
	public function __construct($options=false)
	{
		// Cargamos modelos de BD
		$this->db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$this->pentaho = Zend_Registry::get('pentaho');
		
		// Cargamos modelos de Logs
// 		$this->systemLog = new Application_Plugin_SystemLog ();
// 		$this->userlog = new Application_Plugin_UserLogAction();
// 		$this->userLogAccess = new Application_Plugin_UserLogAccess();
// 		$this->ldapLog = new Application_Plugin_LdapLog();
		
		$this->systemLog = new Application_Plugin_Log_System ();
		$this->userlog = new Application_Plugin_Log_UserAction();
		$this->userLogAccess = new Application_Plugin_Log_UserAccess();
		$this->ldapLog = new Application_Plugin_Log_Ldap();
		
		// Cargamos modelos de datos de usuarios
		$this->userClientData = new Application_Model_Login_Procesos_UserClientData();
		
		// Cargamos motor de traducciones
		$this->translate = Zend_Registry::get ( 'Zend_Translate' );

	}
	
	
	
	
	/**
	 * Parsea el código de error
	 * 
	 * @param array $userData
	 * @param string $modelo
	 */
	protected function procesarError($userData,$modelo='undefined')
	{
		$codeError = $this->__get('codeError');
		
		switch ($codeError)
		{
			case -10:
				self::setMsj($this->translate->_('No dispone los permisos necesarios para acceder a esta aplicación.'),$codeError);
				break;
				
			case -9:
				self::setMsj($this->translate->_('Aplicación cerrada.'),$codeError);
				break;
							
			case 0:
					
				self::logApp(sprintf($this->translate->_('Validación correcta con modelo %s.'),$modelo));
				break;
	
			case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
	
				self::setMsj($this->translate->_('Credenciales incorrectas, intentelo de nuevo o contacte con su administrador de sistema.'),$codeError);
// 				self::setMsj($this->translate->_('El usuario que ha introducido no existe en nuestra Base de datos.'),$codeError);
				self::logApp(sprintf($this->translate->_('El usuario que ha introducido (%s) no existe en la Base de datos %s.'),$userData['username'],$modelo));
				break;
	
			case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
	
				self::setMsj($this->translate->_('Credenciales incorrectas, intentelo de nuevo o contacte con su administrador de sistema.'),$codeError);
// 				self::setMsj($this->translate->_('La clave que ha introducido es incorrecta.'),$codeError);
				self::logApp($this->translate->_('La clave que ha introducido es incorrecta.'));
				break;
	
			default:
				
				self::setMsj($this->translate->_('Credenciales incorrectas, intentelo de nuevo o contacte con su administrador de sistema.'),$codeError);
// 				self::setMsj($this->translate->_('Error de conexión.'),$codeError);
				self::logApp($this->translate->_('Se ha producido un error y el código ('.$codeError.') no está reconocido en la aplicación.'));
				break;
		}
	
		return $codeError;
	}
	
	
	
	
	
	
	
	/**
	 * Verifica si el usuario y el grupo están activos en la app
	 */
	protected function checkAccess($userConnectData,$modelo)
	{
		// Verificamos si la aplicación esta Online
		$app = new Application_Model_DbTable_AppConfig();
		$appConfig = $app->getAppConfig();
	
		// Verificamos si el usuario existe y si está activo.
		$users = new Application_Model_DbTable_Easya_User();
		$usuario = $users->checkExist($userConnectData ['username']);
		
// 		echo '<pre>';
// 		print_r($usuario);
// 		die();
		
		if($usuario):
			$rols = unserialize($usuario['role']);
			$rols_id = unserialize($usuario['role_id']);
		else:
			$rols = array('');
			$rols_id = array('');
		endif;
		
		// Parcheamos al admin
		if($usuario && in_array($rols[(count($rols)-1)],array('God','Admin'))) return self::sessionRegister($usuario,$modelo);
	
		if($app->appStatus()) // Verificamos si la aplicación está online.
		{
			if ($usuario)  // Verificamos si tenemos los datos del usuario.
			{
				return self::sessionRegister($usuario,$modelo); // Conectamos al usuario en la aplicación.
			}
			else return false;
		}
		else
		{
			// Aplicacion offline
			// Elimino los datos de la sesion
			$auth = Zend_Auth::getInstance ();
			$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
			$auth->clearIdentity();
			// Asigno el código del error
			self::setCodeError(-9);
			return false;
		}
	}
	
		
	
	
	
	/**
	 * Registra la sesion del usuario en el sistema
	 * @param array $datos
	 */
	protected function sessionRegister($userData,$modelo)
	{
		//Zend_Session::RememberMe((60 * 60 * 24 * 1));// 1 days
		
		$userModel = new Application_Model_User();
		
		// ACTUALIZAMOS FECHA DE ÚLTIMO ACCESSO DEL USUARIO
		$userModel->setLastLogin($userData['ID']);
		
		$dbroles = new Application_Model_DbTable_AppRoles();
		
			
		$uss = new stdClass();
		$uss->ID				= $userData['ID'];
		$uss->username			= $userData['username'];
		$uss->role				= $userData['role'];
		$uss->role_id			= serialize($dbroles->getIdByArrayName(unserialize($userData['role'])));
		$uss->ldap_location		= $userData['ldap_location'];
		$uss->last_access		= new DateTime($userData['last_access']);
		$uss->created_at		= new DateTime($userData['created_at']);
		$uss->country			= $userData['country'];
		$uss->display_name 		= 'undefined';
	
		// Obtenemos el resto de información del usuario
		switch($modelo)
		{
			case 'Local':
	
				// De la base de datos user_profile
				$userDataM = $userModel->getUserLocalProfile($uss->ID);
				Break;
	
			case 'Ad':
	
				// De Active Directory
				$userDataM = $userModel->getUserAdProfile($userData);
				Break;
					
// 			case 'wiw':
	
// 				// De WiW
// 				$userDataM = $userModel->getUserWiwProfile($userData);
// 				Break;
		}
	
	
		$uss->firstname			= $userDataM['firstname'];
		$uss->surname			= $userDataM['surname'];
		$uss->display_name		= $userDataM['display_name'];
	
		$uss->updated_at		= (isset($userDataM['updated_at']))?new DateTime($userDataM['updated_at']):NULL;
		$uss->email				= (isset($userDataM['email']))?$userDataM['email']:NULL;
		$uss->lang				= (isset($userDataM['lang']))?$userDataM['lang']:'en';
		$uss->department		= (isset($userDataM['department']))?$userDataM['department']:NULL;
		$uss->team				= (isset($userDataM['team']))?$userDataM['team']:NULL;
		$uss->team_id			= (isset($userDataM['team_id']))?$userDataM['team_id']:NULL;
		$uss->OU_ID				= (isset($userDataM['OU_ID']))?$userDataM['OU_ID']:NULL;
		$uss->OU_NAME			= (isset($userDataM['OU_NAME']))?$userDataM['OU_NAME']:NULL;
		$uss->CC				= (isset($userDataM['CC']))?$userDataM['CC']:NULL;
	
	
		// REGISTRAMOS LA SESION
		$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
		$storage->write ($uss); // Guardo los datos del usuario en sesion que acabamos de registrar
		
// 		echo '<pre>';
// 		print_r($uss);
// 		die();

// 		// GUARDAMOS EL LOG DE ACCESO DEL USUARIO
		self::logUserAccess($uss->username);
				
// 		// CARGO EL IDIOMA DEL USUARIO
		self::lang($uss);
	
// 		$rol = new Application_Model_DbTable_AppRoles();
// 		if($uss->role=='God' || $rol->checkActivo($uss->role)) return true;
// 		else
// 		{
// 			$this->baseuri = '/auth/logout';
// 			$this->logout();
// 			return false;
// 		}
	
		return true;
	}
	
	
	protected function registerEasyaUser($usuario)
	{
		// Añadimos los roles que tienen acceso a la aplicación. Si se trata de una app con acceso a todos los usuarios de t-systems añadir solo
		// el rol creado con el modulo
		$AppRoles = array('God','Admin','Portal_Task');
		
		
		if(!is_array($usuario->role)) $usuario->role = unserialize($usuario->role);
		if(!is_array($usuario->role_id)) $usuario->role_id = unserialize($usuario->role_id);
		
// 		echo '<pre>';
// 		print_r($usuario);
// 		die();
		
		foreach($usuario->role as $userRole)
		{
			if(in_array($userRole,$AppRoles))
			{
				$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
				$storage->write ($usuario); // Guardo los datos del usuario en sesion que acabamos de registrar
				
				// GUARDAMOS EL LOG DE ACCESO DEL USUARIO
				self::logUserAccess($usuario->username);
				
				// CARGO EL IDIOMA DEL USUARIO
				self::lang($usuario);
				
				return true;
			}
		}
		
		return false;
	}
	
	
	
	/**
	 * Configuración del idioma de la app para el usuario logueado
	 * @param array $datos
	 */
	private function lang($datos)
	{
		$lang = strtolower($datos->country);
		
		$registry = Zend_Registry::getInstance ();
		$session = new Zend_Session_Namespace ( 'session' );
	
		$translate = $registry->get ( 'Zend_Translate' );
		
		$newLocale = new Zend_Locale($lang);
		$registry->set('Zend_Locale', $newLocale);
		
		$translate->setLocale($lang);
		$registry->set('Zend_Translate', $translate);
	}
	
	
	/**
	 * Logout del sistema
	 */
	public function logout()
	{
		// Borramos la sesión
		$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
		$storage->clear ();
	
		// Borramos los datos de la sesión de la BD
	}
	
	
	
	
	/**
	 * Nos verifica si el usuario está autentificado
	 * @return boolean
	 */
	public static function isLoggedIn()
	{
// 		$hasIdentity = Zend_Auth::getInstance ()->hasIdentity ();
		$auth = Zend_Auth::getInstance ();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		$hasIdentity = $auth->hasIdentity ();
			
		return $hasIdentity;
	}
	
	
	
	
	
	/**
	 * 
	 * Añade una linea al log de la aplicación
	 * @param string $msj
	 * @param sctring $user
	 * @param int $lvl
	 */
	protected function logApp($msj,$lvl='6',$extras=NULL)
	{
		$this->systemLog->setLog($msj,$lvl,$extras);
	}
	
   
	
	
	

    /**
	 * 
	 * Añade una linea al log del usuario
	 * @param string $msj
	 * @param string $user
	 * @param int $lvl
	 */
	protected function logUser($accion,$lvl='6',$extras=NULL)
	{
		$this->userlog->setLog($accion,$lvl,$extras);
	}
	
	
	
	
	/**
	 *
	 * Añade una linea al log del usuario
	 * @param string $msj
	 * @param string $user
	 * @param int $lvl
	 */
	protected function logUserAccess($username)
	{
		$this->userLogAccess->setLog(array('username'=>$username,'ip'=>$this->userClientData->getIP(true),'browser'=>$this->userClientData->getBrowser(),'stage'=>$this->userClientData->getOs()));
	}
	
	
	
	/**
	 *
	 * Añade una linea al log de la aplicación
	 * @param string $msj
	 * @param sctring $user
	 * @param int $lvl
	 */
	protected function logLdap($msj,$extras=NULL)
	{
		$this->ldapLog->setLog($msj,$extras);
	}
	
	
	
	/**
	 * 
	 * Función para debugar arrays
	 */
	public function preprint($elementos, $die=false)
	{
        echo "<pre>";
        print_r($elementos, 1);
        echo "</pre>";
        if ($die) die();
    }
	   
    
    
    /**
     * Añade un msj
     * @param string|array $msj
     */
    protected function setMsj($mensaje,$codeError=false)
    {
    	if(is_array($mensaje))
    	{
    		foreach($mensaje as $msj)
    		{
    			$m['id'] = $codeError;
    			$m['msj'] = $msj;
    			array_push($this->msj,$m);
    		}
    	}
    	else
    	{
    		$m['id'] = $codeError;
    		$m['msj'] = $mensaje;
    		array_push($this->msj,$m);
    	}
    }
    
    
    
    /**
     * Añade el codeError
     * @param string $code
     */
    protected function setCodeError($code=0)
    {
    	$this->codeError = $code;
    }
    
    
    /**
     * Limpia la variable codeError
     * @param string $code
     */
    protected function clearCodeError()
    {
    	$this->codeError = 0;
    }
    
    /**
     * Limpia la variable msj
     * @param string $code
     */
    protected function clearMsjError()
    {
    	$this->msj = array();
    }
    
    
	public function __set($propiedad,$valor) {$this->$propiedad=$valor;}
	public function __get($propiedad) {return $this->$propiedad;}
	public function __isset($propiedad) {return isset($this->$propiedad);}
	public function __unset($propiedad) {unset ($this->$propiedad);}
}