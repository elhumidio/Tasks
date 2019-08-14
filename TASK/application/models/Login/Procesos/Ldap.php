<?php

class Application_Model_Login_Procesos_Ldap extends Application_Model_Login_Abstract
{
	
	/**
	 * Conecta con el servidor de AD mediante LDAP
	 * @param array $userData
	 * @param array $options
	 * @param string $accountCanonicalForm
	 * @return array
	 */
	public function getInfo($userData,$options,$accountCanonicalForm=false)
	{
		
		if(!$accountCanonicalForm) $filtro="(sAMAccountName=".$userData['username'].")";
		else
		{
			$filtro = "()";
			$options['config']['accountCanonicalForm'] = $accountCanonicalForm;
		}
		
		try
		{
			$ldap = new Zend_Ldap($options['config']);
			$ldap->bind($options['auth']['username'], base64_decode($options['auth']['password']));
		}
		catch (Zend_Ldap_Exception $e)
		{
			parent::logLdap($e->getMessage());
		}
		
		$resultado = $ldap->searchEntries($filtro);

		return $resultado;
	}
	
	
	
	/**
	 * Parse, and format a DN string to Array
	 *
	 * Read a LDAP DN, and return an array keys
	 * listing all similar attributes.
	 *
	 * Also takes care of the character escape and unescape
	 *
	 * Example:
	 * CN=username,OU=UNITNAME,OU=Region,OU=Country,DC=subdomain,DC=domain,DC=com
	 *
	 * Would normally return:
	 * Array (
	 *     [count] => 9
	 *     [0] => CN=username
	 *     [1] => OU=UNITNAME
	 *     [2] => OU=Region
	 *     [5] => OU=Country
	 *     [6] => DC=subdomain
	 *     [7] => DC=domain
	 *     [8] => DC=com
	 * )
	 *
	 * Returns instead a manageable array:
	 * array (
	 *     [CN] => array( username )
	 *     [OU] => array( UNITNAME, Region, Country )
	 *     [DC] => array ( subdomain, domain, com )
	 * )
	 *
	 *
	 * @param  string $dn          The DN
	 * @return array
	 */
	public function parseLdapDn($dn)
	{
		$parsr=ldap_explode_dn($dn, 0);
		//$parsr[] = 'EE=Sôme Krazï string';
		//$parsr[] = 'AndBogusOne';
		$out = array();
		foreach($parsr as $key=>$value){
			if(FALSE !== strstr($value, '=')){
				list($prefix,$data) = explode("=",$value);
				$data=preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $data);
				if(isset($current_prefix) && $prefix == $current_prefix){
					$out[$prefix][] = $data;
				} else {
					$current_prefix = $prefix;
					$out[$prefix][] = $data;
				}
			}
		}
		return $out;
	}
	
	
	
	/**
	 * Devuelve el código de país de un array DN
	 * @param array|string $dn
	 */
	public function getCountry($dn)
	{
		if(!is_array($dn))
			$dn = $this->parseLdapDn($dn);
		
		$array = array_reverse($dn['DC']);
		
		return strtoupper($array[0]);
	}
}