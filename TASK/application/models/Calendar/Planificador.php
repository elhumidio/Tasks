<?php
/**
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 1.0 - 21/01/2014
 *
 */

class Application_Model_Calendar_Planificador
{
	/**
	 * Variable que contiene un objeto con los datos del usuario logueado
	 * @var object
	 */
	public $userData;
	
	/**
	 * Variable que contiene los parámetros enviados
	 */
	private $params;
	
	/**
	 * Contiene el log de evento
	 * @var object
	 */
	private $eLog;
	private $tLog;
	private $gLog;
	
	private $Notificaciones;
	
	
	public function __construct($params=array())
	{
		$this->params = $params;
		
		$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
		$namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
		$this->userData = $storage->read($namespace);
		
		$this->eLog = new Application_Model_DbTable_Calendar_EventosLog();
		$this->eLog->__set('username',$this->userData->username);
		$this->eLog->__set('OU_ID',$this->userData->OU_ID);
		
		$this->tLog = new Application_Model_DbTable_Calendar_TareasLog();
		$this->tLog->__set('username',$this->userData->username);
		$this->tLog->__set('OU_ID',$this->userData->OU_ID);
		
		$this->gLog = new Application_Model_DbTable_Calendar_SubgruposLog();
		$this->gLog->__set('username',$this->userData->username);
		$this->gLog->__set('OU_ID',$this->userData->OU_ID);
	}
	
	/**
	 * Obtiene de pentaho los usuarios del grupo del usuario conectado.
	 */
	public function GetGroupMembers()
	{
		$wiw = new Application_Model_Wiw();
		//$resultado = $wiw->GetGrupos('ES003691');
		//$grup_oper = ($this->userData->OU_ID ==='ES003691' )? array($this->userData->OU_ID,'ES003692'): $this->userData->OU_ID;
		$grup_oper = ($this->userData->OU_ID ==='ES003691' )? array('ES003692','ES003691'): $this->userData->OU_ID;
		
		$resultado = $wiw->GetGrupos($grup_oper);
		
		 
		$uin = new Application_Model_DbTable_Calendar_UsuariosInactivos();
		$ignore = $uin->GetAll();
		for($i=0;$i<count($resultado);$i++)
		{
			$clase='drag';
			
			if(in_array($resultado[$i]['uid'], $ignore))
			{
				$clase='ignore';
			}
			
			$resultado[$i]['className'] = $clase;
		}
		
		return $resultado;
	}
	
	/**
	 * Devuelve los Subgrupos del grupo del usuario
	 */
	public function GetSubGroup()
	{
		$db = new Application_Model_DbTable_Calendar_Subgrupos();
		$result = $db->GetSubGrupos($this->userData->OU_ID);
		return $result;
	}
	
	/**
	 * Devuelve los miembros de cada subgrupo
	 */
	public function GetSubGroupMembers($idsubgrupo=false)
	{
		//if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		
		$db = new Application_Model_DbTable_Calendar_Subgrupos();
		$result = $db->GetSubGruposMembers($this->userData->OU_ID,$idsubgrupo);
		
		$array = array();
		if($result)
		{
		foreach ($result as $v):
		$array[$v['id']][] = $v['username'];
		endforeach;
		} else {return false; }
		return $array;
	}
	
	/**
	 * Elimina la relación de un usuario con un subgrupo
	 */
	public function DeleteUserFromSubGroup()
	{
		if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		if(!isset($this->params['iduser'])) return json_encode(array('error'=>array('iduser'=>'Falta dato.')));
		
		$db = new Application_Model_DbTable_Calendar_SubgruposUsername();
		$result = $db->Delete($this->params['idsubgrupo'],$this->params['iduser']);
		if($result):$this->gLog->log('El usuario "'.$this->params['iduser'].'" ha sido eliminado del grupo "'.$this->params['name'].'".');endif;
		return $result;
	}
	
	/**
	 * Añade un Subgrupo
	 */
	public function SetSubGroup()
	{
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		
		$db = new Application_Model_DbTable_Calendar_Subgrupos();
		$result = $db->AddSubGrupos($this->params['name'],$this->userData->OU_ID);
		if($result):$this->gLog->log('El Grupo "'.$this->params['name'].'" ha sido creado.',$result);endif;
		return array($result);
	}
	
	/**
	 * Edita un Subgrupo
	 */
	public function EditSubGroup()
	{
		if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		$db = new Application_Model_DbTable_Calendar_Subgrupos();
		$result = $db->EditSubGrupos($this->params['idsubgrupo'],$this->params['name']);
		$this->gLog->log('El Grupo "'.$this->params['nameold'].'" a cambiado a "'.$this->params['name'].'"',$this->params['idsubgrupo']);
		return array($result);
	}
	
	/**
	 * Elimina un Subgrupo
	 */
	public function DeleteSubGroup()
	{
		if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		$db = new Application_Model_DbTable_Calendar_Subgrupos();
		$result = $db->DeleteSubGrupos($this->params['idsubgrupo']);
		$this->gLog->log('El Grupo "'.$this->params['name'].'" ha sido eliminado.');
		return array($result);
	}
	
	/**
	 * Elimina la relación que hay entre el subgrupo proporcionado y los usuarios
	 */
	public function EmptySubGroup()
	{
		if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		$db = new Application_Model_DbTable_Calendar_SubgruposUsername();
		$result = $db->Delete($this->params['idsubgrupo']);
		$this->gLog->log('El Grupo "'.$this->params['name'].'" ha sido vaciado.');
		return json_encode(array($result));
	}
	
	/**
	 * Relaciona un usuario con un grupo
	 */
	public function RelUserSubGroup()
	{
		if(!isset($this->params['idsubgrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
		if(!isset($this->params['iduser'])) return json_encode(array('error'=>array('iduser'=>'Falta dato.')));
		if(!isset($this->params['ou_id'])) return json_encode(array('error'=>array('ou_id'=>'Falta dato.')));
		if(!isset($this->params['name'])) return json_encode(array('error'=>array('name'=>'Falta dato.')));
		
		$db = new Application_Model_DbTable_Calendar_SubgruposUsername();
		$result = $db->Add($this->params['idsubgrupo'],$this->params['iduser'], $this->params['ou_id']);
		if($result && !isset($result['fail'])) $this->gLog->log('El usuario "'.$this->params['iduser'].'" ha sido añadido al grupo "'.$this->params['name'].'".',$this->params['idsubgrupo']);
		else if (isset($result['fail'])) return json_encode($result);
		
		return array($result);
	}
	
	
	/**
	 * Devuelve los miembros de cada subgrupo
	 */
	public function GetTaskMembers()
	{
		if(!isset($this->params['dateStart'])) return json_encode(array('error'=>array('dateStart'=>'Falta dato.')));
		if(!isset($this->params['dateEnd'])) return json_encode(array('error'=>array('dateEnd'=>'Falta dato.')));
	
		$ds = new DateTime($this->params['dateStart']);
		$de = new DateTime($this->params['dateEnd']);
		
		$db = new Application_Model_DbTable_Calendar_Tareas();
		$result = $db->GetTaskGroup($this->userData->OU_ID,'huerfanas');
		$resultb = $db->GetTaskGroup($this->userData->OU_ID,'asignadas',$ds->format('Y-m-d H:i:s'),$de->format('Y-m-d H:i:s'));
		
		$pendientes = array();
		$asiganadas = array();
		
		foreach ($result as $v):$pendientes[$v['origen']][] = $v;endforeach;
		
		foreach ($resultb as $w):$asiganadas[$w['username']][] = $w;endforeach;
	
		return array('asiganadas'=>$asiganadas,'pendientes'=>$pendientes);
	}
	
	/**
	 * Devuelve la lista de origen de datos
	 */
	public function GetOrigenList()
	{
		$db = new Application_Model_DbTable_Calendar_Tareas();
		$result = $db->GetOrigenes();
		return $result;
	}
    
	/**
	 * De vuelve las estadisticas de las tareas
	 */
	public function GetTaskStats()
	{
		$db = new Application_Model_DbTable_Calendar_Tareas();
		
		$salida['total'] = count($db->GetTaskGroup($this->userData->OU_ID,'all'));
		$salida['pendientes'] = count($db->GetTaskGroup($this->userData->OU_ID,'huerfanas'));
		$salida['asignadas'] = count($db->GetTaskGroup($this->userData->OU_ID,'asignadas'));
		$salida['programadas'] =  count($db->GetTaskGroup($this->userData->OU_ID,'programadas'));
		//$salida['asignadas'] -= $salida['programadas'];
		
		return $salida;
	}
	
	public function __set($propiedad,$valor) {$this->$propiedad=$valor;}
	public function __get($propiedad) {return $this->$propiedad;}
	public function __isset($propiedad) {return isset($this->$propiedad);}
	public function __unset($propiedad) {unset ($this->$propiedad);}
}