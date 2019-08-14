<?php
class Application_Model_DbTable_Calendar_Subgrupos extends Zend_Db_Table {
	
	protected $_name = "cal_subgrupos";
	protected $_primary = 'id';
	
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	public function __set($propiedad,$valor) {
		$this->$propiedad = $valor;
	}
	
	/**
	 * Devuelve los subgrupos del grupo porporcionado
	 * @param string $OU_ID
	 */
	public function GetSubGrupos($OU_ID)
	{
		try {
			$sql = parent::select()->from($this->_name,array('id','name'))->where('OU_ID=?',$OU_ID)->order('name ASC');
			$result = parent::fetchAll($sql);
			return $result->count()>0?$result->toArray():false;
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}	
	}
	
	public function GetSubGruposMembers($OU_ID,$idsubgrupo=false)
	{
		try {
			$dbRel = new Application_Model_DbTable_Calendar_SubgruposUsername();
			
			$sql = parent::select()->from(array('a'=>$this->_name),array('id'))->setIntegrityCheck(false)
									->joinLeft(array('b'=>$dbRel->__get('_name')), 'a.id=b.idSubGrupo',array('username'))
									->where('a.OU_ID=?',$OU_ID)
									->order('b.username ASC');
			if($idsubgrupo){
				$sql->where('b.idSubGrupo=?',$idsubgrupo);
			}
			
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
	public function AddSubGrupos($name,$OU_ID)
	{
		try {
			return parent::insert(array('name'=>$name,'OU_ID'=>$OU_ID));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Edita un Subgrupo
	 * @param int $name
	 * @param string $name
	 */
	public function EditSubGrupos($id,$name)
	{
		try {
			return parent::update(array('name'=>$name),array('id=?'=>$id));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Elimina un Subgrupo
	 * @param int $id
	 */
	public function DeleteSubGrupos($id)
	{
		try {
			return parent::delete(array('id=?'=>$id));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Devuelve los grupos a los que pertenece el usuario dado para la subquery
	 */
	public function GetGruposDelUsuarioSubquery($username)
	{
		try {
			$sql = parent::select()->from($this->_name,array('id'))->where('username=?',$username);
			$result = parent::fetchAll($sql);
			return $result->count()>0?$result->toArray():false;
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
}

?>