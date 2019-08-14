<?php
class Application_Model_DbTable_Calendar_EventosOpciones extends Zend_Db_Table {
	
	protected $_name = "cal_eventos_opciones";
	protected $_primary = array('idEvento','option');
	
	
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	/**
	 * Devuelve la lista de opciones de un evento
	 * @param array $Event
	 */
	public function getEventValues($Event)
	{
		try{
			$select = parent::select()->from($this,array('option','value'))->where('idEvento = ?',$Event['id']);
			$result = parent::fetchAll($select)->toArray();
			if(count($result)>0):
			foreach($result as $row):$Event[$row['option']] = $row['value']; endforeach;
			endif;
			return $Event;
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Guarda una opción y su valor
	 * @param int $id
	 * @param string $option
	 * @param string $value
	 */
	public function SetEventValue($id,$option,$value)
	{
		try{
			return parent::insert(array('idEvento'=>$id,'option'=>$option,'value'=>$value));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	
	/**
	 * Actualiza una opción
	 * @param int $id
	 * @param string $value
	 * #return array
	 */
	public function UpdateEventValue($id,$value)
	{
		$data = array('value'=>$value);
		$where['idEvento = ?'] = $id;
			try{
				parent::update($data, $where);
			}
			catch(Exception $e){
				return array('fail'=>array($e->getCode()=>$e->getMessage()));
			}
	}
	
	/**
	 * Borra una opción y su valor
	 * @param int $id
	 * @param string $option
	 * @param string $value
	 */
	public function RemoveEventValue($id,$option,$value)
	{
		try{
			return parent::delete(array('idEvento=?'=>$id,'`option`=?'=>$option,'value=?'=>$value) );
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
}

?>