<?php

class Application_Model_Login_LoginSso extends Application_Model_Login_Abstract
{
	const __MODELO__ = 'Sso';
	
	/**
	 * Nos autologuea en la aplicación
	 * @param string $sso
	 */
	public function login($sso)
	{
		$db = Zend_Registry::get('easya'); // utilizo la BD del Easy.A
	
		$sql = $db->select()->from('user_session')->where('session_id=?',$sso);
		$result = $db->fetchRow($sql);
			
		$datos_array = $this->unserialize_session_data($result['session_data']);

// 		echo '<pre>';
// 		print_r($datos_array);
// 		die();

		if(isset($datos_array[NAMESPACE_BASE]['storage'])):
			if(parent::registerEasyaUser($datos_array[NAMESPACE_BASE]['storage'])): return true;
			else:
				parent::setCodeError(-10);
				$codeError = $this->procesarError(array());
			endif;
		endif;
	}
	
	
	/**
	 * Parsea los datos de una sesion y devuelve el array
	 * @param string $serialized_string
	 * @return multitype:NULL
	 */
	private function unserialize_session_data( $serialized_string )
	{
		$variables = array();
		$a = preg_split("/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for($i=0;$i<count($a);$i=$i+2):
			$variables[$a[$i]] = unserialize($a[$i+1]) or die('ERROR: serialized string break in <i>unserialize_session_data</i> function.');
		endfor;
		return($variables);
	}
}