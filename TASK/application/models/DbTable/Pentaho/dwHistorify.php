<?php
class Application_Model_DbTable_Pentaho_dwHistorify extends Zend_Db_Table_Abstract
{
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'kpi' );
		parent::_setupDatabaseAdapter ();
	}
	
	
	/**
	 * Verifica si ya existe un kpi
	 * @param array $params
	 * @return boolean
	 */
	public function isExist($params)
	{
		if(!isset($params['CLIENTE'])) {echo 'Falta Cliente'; return true;}
		if(!isset($params['KPI'])) {echo 'Falta KPI'; return true;}
		if(!isset($params['TIPO'])) {echo 'Falta Tipo'; return true;}
		if(!isset($params['FECHA'])) {echo 'Falta Fecha'; return true;}
		if(!isset($params['GRUPO'])) {echo 'Falta Grupo'; return true;}
		
		$select = parent::select()->where('UCASE(CLIENTE) COLLATE \'latin1_general_cs\' = ? COLLATE \'utf8_general_ci\'', strtoupper($params['CLIENTE']))
									->where('KPI = ?', $params['KPI'])
									->where('TIPO = ?', $params['TIPO'])
									->where('FECHA = ?', $params['FECHA']);
	
		if(is_object($params['GRUPO'])) $select->where('GRUPO IS NULL');
		else $select->where('GRUPO = ?', $params['GRUPO']);
	
		$result = parent::fetchAll($select);
		
// 		echo $select->__toString();
// 		die();
		
		return $result->count()==0?false:true;
	}
	
}

?>