<?php
class Application_Model_DbTable_Easya_User extends Zend_Db_Table {
	
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Verifica si un usuario existe en nuestra BD y nos devuelve
	 * sus datos. En caso contrario nos devuelve false.
	 * 
	 * @param string $username
	 */
	public function checkExist($username)
	{
		$sql = $this->select()->where('username=?',$username);
		$result = $this->fetchRow($sql);
		
		return ((count($result)>0)?$result->toArray():false);
	}
	
	
	public function checkUnique($username) 
	{
		
		$select = $this->_db->select ()->from ( $this->_name, array ('username' ) )->where ( 'username=?', $username );
		$result = $this->getAdapter ()->fetchOne ( $select );
		
		if ($result) return true;
		
		return false;
	
	}
	
	public function checkPassword($username,$password)
	{
		$select = $this->_db->select()->from( $this->_name, array ('password' ) )->where ( 'username=?', $username );
		$result = $this->getAdapter()->fetchOne( $select );
		
		if ($result==$password) return true;
		
		return false;
	}
	
	public function updateDisplayName($username,$display_name)
	{
		$datos = self::checkExist($username);
		if($datos && is_null($datos['display_name']))
		{
			parent::update(array('display_name'=>ucwords(mb_strtolower($display_name, 'UTF-8'))),array('username = ?'=>$username));
		}
	}
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>