<?php
class Application_Model_DbTable_Pentaho_KpiAudits extends Zend_Db_Table_Abstract
{
	protected $_name = "qdc_audit";
	protected $_primary = 'CLIENTE';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'kpi' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 *@return array
	 */
	public function getAll($client=false)
	{
		$sql = parent::select();
	
		if($client)
		{
			$sql->where('CLIENTE = ?',$client);
		}
	
		$result = parent::fetchAll($sql)->toArray();
		return count($result)==0?array():$result;
	}
	
}

?>