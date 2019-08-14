<?php
class Application_Model_DbTable_Easya_RssLinks extends Zend_Db_Table {
	
	protected $_name = "rss_links";
	protected $_primary = 'username';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	public function checkKey($username,$key)
	{
		$select = parent::find($username);
		$row = $select->current();
		return ($row->key==$key)?true:false;
	}
	
	/**
	 * Crea una nueva key
	 * @param string $username
	 * @param string $OU_ID
	 * @return int or bool
	 */
	public function createKey($username,$OU_ID)
	{
		$key = self::getByUsername($username);
		if(!$key)
		{
			$key = base64_encode($username.'|||'.$OU_ID.'|||'.time());
			parent::insert(array('username'=>$username,'key'=>$key));
		}
		return $key;
	}
	
	/**
	 * Actualiza un msj
	 * @param string $username
	 * @param string $OU_ID
	 */
	public function updateKey($username,$OU_ID)
	{
		self::deleteKey($username);
		return self::createKey($username,$OU_ID);
	}
	
	/**
	 * Elimina un msj.
	 * @param int $id
	 * @return array
	 */
	public function deleteKey($username)
	{
		$select = parent::find($username);
		$row = $select->current();
		$row->delete();
		return $row->delete();
	}
	
	
	/**
	 * Devuelve la key de un username
	 * @param string $username
	 * @return string
	 */
	public function getByUsername($username)
	{
		$where = parent::getAdapter()->quoteInto('username = ?', $username);
		$result = parent::fetchRow($where);
		return (count($result)==0)?false:$result['key'];
	}
	
	
	/**
	 * Actualiza el campo
	 * @param int $id
	 * @param string $campo
	 *
	 */
	public function updateGral($primary,$campos)
	{
		$select = parent::find($primary);
		$row = $select->current();
	
		foreach($campos as $k=>$v)
			$row->$k = $v;
	
		return $row->save();
	}
		
}

?>