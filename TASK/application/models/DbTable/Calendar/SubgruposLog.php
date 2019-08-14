<?php
class Application_Model_DbTable_Calendar_SubgruposLog extends Zend_Db_Table {
	
	protected $_name = "cal_subgrupos_log";
	protected $_primary = array('idSubGrupo','username');
	
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
	 * @param int $idSubGrupo
	 * @param string $msj
	 */
	public function log($msj, $idSubGrupo=NULL)
	{
		try{
			return parent::insert(array('idSubGrupo'=>$idSubGrupo,'username'=>$this->username,'OU_ID'=>$this->OU_ID,'msj'=>$msj));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
}

?>