<?php
class Application_Model_DbTable_Easya_AppAplicaciones extends Zend_Db_Table {
	
	protected $_name = "app_aplicaciones";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	public function getApp($id)
	{
		$where = parent::select()->where('ID=?',$id);
		$result = parent::fetchRow($where);
		return $result->toArray();
	}
	
}

?>