<?php
class Application_Model_DbTable_Easya_AppConfig extends Zend_Db_Table {
	
	protected $_name = "app_config";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Nos devuelve el estado de la aplicación.
	 * true si Opend / false si Closed
	 */
	public function appStatus()
	{
		$sql = parent::select()->where('name=?','app_status');
		$result = parent::fetchRow($sql);
		
		if($result['value']=='online') return true;
		else return false;
		
	}
	
	
	/**
	 * Nos devuelve un objeto con los parametros de configuración de la aplicación
	 */
	public function getAppConfig()
	{
		$objeto = new stdClass();
		
		$result = parent::fetchAll()->toArray();
		
		foreach($result as $val)
			$objeto->$val['name'] = $val['value'];
		
		return $objeto;
	}
	
}

?>