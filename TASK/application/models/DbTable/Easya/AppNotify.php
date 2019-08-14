<?php
class Application_Model_DbTable_Easya_AppNotify extends Zend_Db_Table {
	
	protected $_name = "log_notify";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
	/**
	 * Crea un nuevo msj
	 */
	public function createMsj($msj,$username,$portal,$mostrado='N')
	{
		$datos = array('msj'=>$msj,'username'=>$username,'portal'=>$portal,'mostrado'=>$mostrado);
		return parent::insert($datos);
	}
	
	/**
	 * Actualiza el campo
	 * @param int $id
	 * @param string $campo
	 *
	 */
	public function updateGral($id,$campos)
	{
		$select = parent::find($id);
		$row = $select->current();
	
		foreach($campos as $k=>$v)
			$row->$k = $v;
	
		return $row->save();
	}
	
	
	/**
	 * Devuelve las notificaciones del usuario
	 * @param string $username
	 * @param string $app
	 * @return array
	 */
	public function GetUserNotificationsPending($username,$app='all')
	{
		$salida = array();
		$select = parent::select();
		
		if($app=='all') {
			$select->where('username = ?', $username)->where('portal = ?','all')->where('mostrado = ?','N')
				   ->orWhere('username = ?', $username)->where('portal = ?','all')->where('aceptado = ?','N');
		}else{
			$select	->where('username = ?', $username)->where('portal = ?','all')->where('mostrado = ?','N')
					->orWhere('username = ?', $username)->where('portal = ?',$app)->where('mostrado = ?','N')
					->orWhere('username = ?', $username)->where('portal = ?','all')->where('aceptado = ?','N')
					->orWhere('username = ?', $username)->where('portal = ?',$app)->where('aceptado = ?','N');
		}
		
		$result = parent::fetchAll($select)->toArray();
		
		if(count($result)>0)
		{
			foreach($result as $row)
			{
				self::updateGral($row['ID'],array('mostrado'=>'Y'));
				array_push($salida,$row);
			}
		}
		
		return $salida;
	}
}

?>