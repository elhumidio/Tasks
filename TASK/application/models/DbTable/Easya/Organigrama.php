<?php
class Application_Model_DbTable_Easya_Organigrama extends Zend_Db_Table {
	
	protected $_name = "data_portal_wiw_org";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Devuelve los datos de la peticion
	 * @param int $ou_id
	 */
	public function getById($ou_id)
	{
		$where = parent::getAdapter()->quoteInto('OU_ID=?', $ou_id);
		$result = parent::fetchRow($where);
	
		return $result;
	}
	
	
	public function checkActivo($ou_id)
	{
		$sql = parent::select()->where('OU_ID=?',$ou_id);
	
		$result = self::fetchRow($sql);
	
		return (($result['estado']=='on')?true:false);
	}
	
	
	public function getCountryCode()
	{
		$select = parent::select()->distinct();
		$result = parent::fetchAll($select)->toArray();
		return $result;
	}
	
}

?>