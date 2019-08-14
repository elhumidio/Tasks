<?php
class Application_Model_DbTable_Calendar_TareasLog extends Zend_Db_Table {
	
	protected $_name = "cal_tareas_log";
	protected $_primary = array('idTarea','username');
	
	private $username;
	private $OU_ID;
		
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	public function __set($propiedad,$valor) {
		$this->$propiedad = $valor;
	}
	
	/**
	 * Inserta una fila
	 * @param int $idTarea
	 * @param string $msj
	 */
	public function log($idTarea,$msj)
	{
		try{
			return parent::insert(array('idTarea'=>$idTarea,'username'=>$this->username,'OU_ID'=>$this->OU_ID,'msj'=>$msj));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}

	/**
	 * retorna log tareas
	 * @param string $idTarea
	 */
	public function getTareasLog($idTarea)
	{
		$this->$_name = "cal_tareas_log";
	    $this->$_primary = "idTarea";
	    $select = parent::select()->where('idTarea =?',$idTarea)->order('id asc');
	    $res = parent::fetchAll($select)->toArray();
	    return $res;
	    
	}
}

?>