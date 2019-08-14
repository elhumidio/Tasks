<?php
class Application_Model_DbTable_Pentaho_KpiDescripcion extends Zend_Db_Table_Abstract
{
	protected $_name = "correos_qdc_kpi_descripcion";
	protected $_primary = 'KPI';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'kpi' );
		parent::_setupDatabaseAdapter ();
	}
}

?>