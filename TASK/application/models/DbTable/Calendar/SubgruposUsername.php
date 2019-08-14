<?php
class Application_Model_DbTable_Calendar_SubgruposUsername extends Zend_Db_Table {
	
	protected $_name = "cal_subgrupos_username";
	protected $_primary = array('idSubGrupo','username','OU_ID');
	
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	public function __set($propiedad,$valor) {
		$this->$propiedad = $valor;
	}
	
	/**
	 * Devuelve los usuarios del grupo
	 * @param int $idSubGrupo
	 */
	public function GetUsersInGroup($idSubGrupo)
	{
		try{
			$sql = parent::select()->from($this->_name,array('username'))->where('idSubGrupo=?',$idSubGrupo);
			$result = parent::fetchAll($sql);
			return $result->count()>0?$result->toArray():false;		
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Devuelve los grupos de un usuario
	 * @param string $username
	 */
	public function GetGroupsInUser($username)
	{
		try{
			$sql = parent::select()->from($this->_name,array('idSubGrupo'))->where('username=?',$username);
			$result = parent::fetchAll($sql);
			return $result->count()>0?$result->toArray():false;
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Añade un Subgrupo
	 * @param string $name
	 * @param sctring $OU_ID
	 */
	public function Add($idSubGrupo,$username,$OU_ID)
	{
		try{
			return parent::insert(array('idSubGrupo'=>$idSubGrupo,'username'=>$username,'OU_ID'=>$OU_ID));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	
	/**
	 * Elimina un Subgrupo
	 * @param int $id
	 */
	public function Delete($idSubGrupo,$username='%%')
	{
		try{
			return parent::delete(array('idSubGrupo=?'=>$idSubGrupo,'username LIKE (?)'=>$username));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
// 	public function EmptySubGroup($idSubGrupo)
// 	{
// 		return parent::delete(array('idSubGrupo=?'=>$idSubGrupo));
// 	}
}

?>