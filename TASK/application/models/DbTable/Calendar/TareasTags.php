<?php
class Application_Model_DbTable_Calendar_TareasTags extends Zend_Db_Table {
	
	protected $_name = "cal_tareas_tags";
	protected $_primary = 'idTarea';
	
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
}

?>