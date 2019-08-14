<?php

class Application_Model_Login_Procesos_WhoIsWho extends Application_Model_Login_Abstract
{
	
	/**
	 * Función que obtiene la información personal del usuario
	 * @param array $datos
	 * @return array
	 */
	public function getUserInfo($username)
	{
		/// Obtenemos los datos restantes del usuario desde pentajo
		$resultado = self::getUserFromPentahoByUsername($username);

		if($resultado)		
		{
			$usuario = array();
			$usuario['nombres'] 		= $resultado['Emp_givenname'];
			$usuario['apellidos'] 		= $resultado['Emp_surname'];
			$usuario['email'] 			= $resultado['Emp_Email'];
				
			$usuario['homephone'] 		= (isset($resultado['homephone']))?$resultado['homephone']:NULL;
			$usuario['telephonenumber'] = (isset($resultado['telephonenumber']))?$resultado['telephonenumber']:NULL;
			$usuario['mobile'] 			= (isset($resultado['mobile']))?$resultado['mobile']:NULL;
				
			$usuario['username'] 		= $username;
			$usuario['CC'] 				= $resultado['Emp_CC'];
			$usuario['OU_ID'] 			= $resultado['OU_ID'];
			
			$dbgp = new Application_Model_DbTable_Organigrama();
			$info = $dbgp->getById($resultado['OU_ID']);
			
			$usuario['OU_NAME'] 		= $info['OU_Name'];
			$usuario['role'] 			= $info['role'];
			
			return $usuario;
		}
		
		return false;
	}
	
	
	
	/**
	 * Devuelve el email de un usuario
	 * @param array $datos
	 */
	private function getEmailFromLdap($datos,$servidorldap = 'tse')
	{
		$options = Zend_Registry::get ( 'configuracion.ini' );

		// El filtro es tan simple por que en la configuración del LDAp la baseDn ya
		// incluye las categorías por lo que se puede hacer una búsqueda simple
		$filtro="(samaccountname=".$datos['username'].")";
		
		$ldap = new Zend_Ldap($options['ldap'][$servidorldap]);
		$ldap->bind($options['ldap']['opt-'.$servidorldap]['username'], base64_decode($options['ldap']['opt-'.$servidorldap]['password']));
		
		$resultado = $ldap->searchEntries($filtro);
		
// 		echo '<pre>';
// 		print_r($resultado);
// 		die();
		return $resultado[0]['mail'][0];
	}
	
	
	/**
	 * Devuelve los datos del usuario dando el email
	 * @param string $email
	 */
	private function getUserFromPentahoByUsername($uid)
	{
		try{
			$db = Zend_Registry::get('pentaho');
			$sql = $db->select()->from('data_portal_wiw_emp')->where('uid=?',$uid);
			$result = $db->fetchRow($sql);
		}catch (Zend_Exception $e){
			echo $e->getMessage();
			die();
		}
		
		
		return (count($result)>0)?$result:false;
	}
	
	
}