<?php
class Application_Model_DbTable_Calendar_UsuariosInactivos extends Zend_Db_Table {
	
	protected $_name = "cal_user_disabled";
	protected $_primary =array('username');
	
	public function GetAll()
	{
		$salida = array();
		
		$s = self::fetchAll();
		
		for($i=0;$i<$s->count();$i++)
		{
			array_push($salida, $s[$i]['username']);
		}
		
		return $salida;
	}
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>