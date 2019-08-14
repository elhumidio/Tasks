<?php
class Application_Model_DbTable_Easya_AppAplicacionesCampos extends Zend_Db_Table {
	
	protected $_name = "app_aplicaciones_campos";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Obtenemos campos de una aplicación
	 * @param int $id_aplicaciones
	 */
	public function getCampos($id_aplicaciones)
	{
		$where = parent::select()->where('id_aplicaciones=?',$id_aplicaciones);
		$result = parent::fetchAll($where);
		return $result->toArray();
	}
	
}

?>