<?php
class Application_Model_DbTable_Easya_AppDelegations extends Zend_Db_Table {
	
	protected $_name = "app_delegations";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Inserta en la tabla la info de delegaciones
	 * @param array $datos
	 */
	public function importFromWiW($datos)
	{
		foreach($datos as $dato)
		{
			$array = array();
			$array['OU_Name'] = $dato['OU_Name'];
			$array['OU_Shortname'] = $dato['OU_Shortname'];
			$array['OU_ID'] = $dato['OU_ID'];
			
			parent::insert($array);
		}
		
	}
	
	
	public function getListado()
	{
		$sql = parent::select()->where('status=?','on')->order('OU_Name ASC');
		return parent::fetchAll($sql)->toArray();
	}
}

?>