<?php
class Application_Model_DbTable_Calendar_Comentarios extends Zend_Db_Table {
	
	protected $_name = "cal_comentarios";
	protected $_primary = 'id';
	
	
		
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>