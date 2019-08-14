<?php
class Application_Model_DbTable_Easya_AppDelegationsRelAplicaciones extends Zend_Db_Table {
	
	protected $_name = "app_delegations_rel_aplicaciones";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	public function checkRel($id_delegations,$id_aplicaciones)
	{
		$where = parent::select()->where('id_delegations=?',$id_delegations)->where('id_aplicaciones=?',$id_aplicaciones);
		$result = parent::fetchRow($where);
		return (count($result)==0)?false:true;
	}
	
	
	
	/**
	 * Obtenemos el listado por plantilla ID
	 * @param unknown_type $id_delegations
	 */
	public function getDelegacion($id_delegations)
	{
		$where = parent::select()->where('id_delegations=?',$id_delegations)->order('id_aplicaciones ASC');
		$result = parent::fetchAll($where);
		return $result->toArray();
	}
}

?>