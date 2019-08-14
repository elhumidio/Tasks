<?php
class Application_Model_DbTable_Calendar_UsuariosTareas extends Zend_Db_Table {
	
	protected $_name = "cal_tareas_asignacion";
	protected $_primary =array('idTarea','username','OU_ID');
	
	public function AsignarUsuario($idTarea,$username)
	{
		try{
			return parent::insert(array('idTarea'=>$idTarea,'username'=>$username));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	public function AsignarSubgrupo($idTarea,$idSubgrupo)
	{
		try{
			return parent::insert(array('idTarea'=>$idTarea,'idSubGrupo'=>$idSubgrupo));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	public function Desasignarusuario($idTarea,$username)
	{
		try{
			return parent::delete(array('idTarea=?'=>$idTarea,'username=?'=>$username));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	public function DesasignarSubgrupo($idTarea,$idSubgrupo)
	{
		try{
			return parent::delete(array('idTarea=?'=>$idTarea,'idSubGrupo=?'=>$idSubgrupo));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>