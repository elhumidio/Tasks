<?php
class Application_Model_DbTable_AppRecursos extends Zend_Db_Table {
	
	protected $_name = "app_recursos";
	protected $_primary = 'ID';
	
	
	/**
	 * Devuelve el nombre de un recurso
	 * @param int $ID
	 */
	public function getName($ID)
	{
		if(is_null($ID)) return NULL;
	
		$select = parent::find($ID);
		return $select[0]['controller'];
	}
	
	
	public function checkExiste($module,$controller,$action=NULL)
	{
		$select = parent::select()->where('module=?',$module)->where('controller=?',$controller);
		
		// verifico si tengo un controller
		if(isset($action)) $select->where('action=?',$action);
		else $select->where('action IS NULL');
		
		
		$result = parent::fetchRow($select);
		
		return count($result)==0?true:false;
	}
	
	
	public function refreshRecursos($module,$controller,$action=NULL)
	{
		if(self::checkExiste($module,$controller,$action))
		{
			return parent::insert(array('module'=>$module,'controller'=>$controller,'action'=>$action));
		}
		
		return false;
	}
	
	public function getAll()
	{
		$select = parent::select()->order('controller ASC');
		$result = parent::fetchAll($select)->toArray();
		return $result;
	}
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>