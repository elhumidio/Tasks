<?php
class Application_Model_DbTable_Pentaho_dwItc extends Zend_Db_Table_Abstract
{
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'pentaho' );
		parent::_setupDatabaseAdapter ();
	}
}

?>