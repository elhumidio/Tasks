<?php
class Application_Model_DbTable_Calendar_Tags extends Zend_Db_Table {
	
	protected $_name = "cal_tags";
	protected $_primary = 'id';
	
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
}

?>