<?php
/**
 *
 * @author 9jibadia
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * MenuActivo helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_TareasExternas {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	const _DELAYED_ = 'delayed';
	const _LIMIT_ = 'limite';
	const _ALERT_ = 'alerta';
	const _ONTIME_ = '';
	
	/**
	 *  
	 */
	public function TareasExternas() {
		
		return $this;
	}
	
	
	/**
	 * Devuelve los campos de la tarea cuyo $idTarea es proporcionado,
	 * en caso de no proporcionarlo se devuelve la lista de tareas.
	 */
	public function GetBitacora()
	{
		
		$TareasDb = new Application_Model_DbTable_Calendar_Tareas();
		$EventsDb = new Application_Model_DbTable_Calendar_Eventos();
		$UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosTareas();
		$EventsLogDb = new Application_Model_DbTable_Calendar_EventosLog();
		$hour_ago = date( "Y-m-d H:i:s",strtotime('-14 hour'));
		$hour_after = date( "Y-m-d H:i:s",strtotime('+24 hour'));
		$selectb = $TareasDb->select()
							->setIntegrityCheck(false)
							->from(array('a'=>$TareasDb->__get('_name')),array('idTarea'=>'id','limite','title','OU_ID'))
							->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idTarea',array('username'))
							->joinLeft(array('b'=>$EventsDb->__get('_name')),'a.id=b.idTarea',array('id','start'=>new Zend_Db_Expr('DATE_FORMAT(start,\'%d/%m/%Y %H:%i\')'),'end'=>new Zend_Db_Expr('DATE_FORMAT(end,\'%d/%m/%Y %H:%i\')'),'origen','allDay','refer','description','status','cliente','centro','turno'))
							->joinLeft(array('d'=>$EventsLogDb->__get('_name')),'b.id=d.idEvento',array('Fecha Log'=>new Zend_Db_Expr('DATE_FORMAT(fecha,\'%d/%m/%Y %H:%i\')'),'User Log'=>'username','Message Log'=>'msj'))							
							//->where('c.username = ?',$this->view->user->username)
							->where('a.programada IS NOT NULL')
							->where('b.status NOT IN(\'Cancelado\',\'Finalizado\')')
							->where(new Zend_Db_Expr("b.start BETWEEN '$hour_ago' AND '$hour_after' "))
							->group('a.id')
							
							->order(array('b.start','d.fecha DESC'))
		;
		//die($selectb->__toString());
		$listb = $TareasDb->fetchAll($selectb)->toArray();
		
		
		$TareasProgramadas = array();
		$TareasProgramadas['count'] = count($listb);
		$TareasProgramadas['list'] = self::setTiming($listb);
		$TareasProgramadas['Estado'] = 'Prueba de ejecución...';
// 		$TareasProgramadas['list'] = $this->view->partialLoop('partials/tareasList.phtml',self::setTiming($listb));
		
		$Tareas = array('programadas'=>$TareasProgramadas);
		echo json_encode($Tareas);
		
// 		return $this->view->partialLoop('partials/tareasList.phtml',self::setTiming($Tareas));
	}
	
	
	/**
	 * Devuelve todas las tareas que no esten asignadas a un evento
	 */
	public function Get($idtarea = false)
	{
// 		Application_Plugin_LogFirebug::log('Test');
		echo (isset($idtarea)?self::GetTask($idtarea):self::GetAllTask());
	}
	
	
	/**
	 * Devuelve todas las tareas que no esten asignadas a un evento
	 */
	private function GetAllTask()
	{
		$TareasDb = new Application_Model_DbTable_Calendar_Tareas();
		$EventsDb = new Application_Model_DbTable_Calendar_Eventos();
		$UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosTareas();
		$TareasTagDb = new Application_Model_DbTable_Calendar_TareasTags();

		$select = $TareasDb->select()
						->setIntegrityCheck(false)
						->from(array('a'=>$TareasDb->__get('_name')),array('idtarea'=>'id','limite','title','origen','OU_ID','creada','limite'))
						->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
						->joinLeft(array('b'=>$EventsDb->__get('_name')),'a.id=b.idtarea',NULL)
						->where('c.username = ?',$this->view->user->username)
						->where('b.idtarea IS NULL')
						->where('a.programada IS NULL')
						->where('a.finalizada IS NULL')
						->order('a.limite ASC');
		
		$list = $TareasDb->fetchAll($select)->toArray();
		$TareasPendientes = array();
		$TareasPendientes['count'] = count($list);
		$TareasPendientes['list'] = self::setTiming($list);
		
		$selectb = $TareasDb->select()
							->setIntegrityCheck(false)
							->from(array('a'=>$TareasDb->__get('_name')),array('idtarea'=>'id','limite','title','origen','OU_ID','creada','limite'))
							->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
							->joinLeft(array('b'=>$EventsDb->__get('_name')),'a.id=b.idtarea',array('id','start'=>new Zend_Db_Expr('UNIX_TIMESTAMP(b.start)'),'end'=>new Zend_Db_Expr('UNIX_TIMESTAMP(b.end)'),'origen','allDay'))
							->where('c.username = ?',$this->view->user->username)
							->where('a.programada IS NOT NULL')
							->where('a.finalizada IS NULL')
							->order('a.limite ASC');
		$listb = $TareasDb->fetchAll($selectb)->toArray();

		
		$TareasProgramadas = array();
		$TareasProgramadas['count'] = count($listb);
		$TareasProgramadas['list'] = self::setTiming($listb);		

		$selectc = $TareasDb->select()
						->setIntegrityCheck(false)
						->from(array('a'=>$TareasDb->__get('_name')),array('idtarea'=>'id','limite','title','origen','OU_ID','creada','limite'))
						->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
						->joinleft(array('b'=>$TareasTagDb->__get('_name')),'a.id=b.idTarea',null)
						->joinleft(array('d'=>'cal_tags'),'d.id=b.idTags',array('tags'=>new Zend_Db_Expr('group_concat(tag separator \'@@@\')')))
						->where('a.OU_ID=?',$this->view->user->OU_ID)
						->where('c.username IS NULL')
						->where('a.finalizada IS NULL')
						->order('a.limite ASC')
						->group('a.id');

		
		$listc = $TareasDb->fetchAll($selectc)->toArray();
		
		$TareasHuerfanas = array();
		$TareasHuerfanas['count'] = count($listc);
		$TareasHuerfanas['list'] = $listc;
		
		$Tareas = array('programadas'=>$TareasProgramadas,'pendientes'=>$TareasPendientes,'huerfanas'=>$TareasHuerfanas);
		

		return json_encode($Tareas);
	}
	
	
	/**
	 * Devuelve la informacion de la tarea 
	 * @param int $idtarea
	 */
	private function GetTask($idtarea)
	{	
		$TagsDb = new Application_Model_DbTable_Calendar_Tags();
		$TareasDb = new Application_Model_DbTable_Calendar_Tareas();
		$ComentatiosDb = new Application_Model_DbTable_Calendar_Comentarios();		
		$TareasTagDb = new Application_Model_DbTable_Calendar_TareasTags();
		
		$selectB = $ComentatiosDb->select()
								->from(array('c'=>$ComentatiosDb->__get('_name')),array('idRelacion','comentarios'=>new Zend_db_Expr('COUNT(c.idRelacion)')))
								->where('c.apartado = ?','tareas')
								->where('c.borrado = ?','N')
								->group(array('c.idRelacion'));
		
		
		$select = $TareasDb->select()->setIntegrityCheck(false)
							->from(array('a'=>$TareasDb->__get('_name')),array('id','limite','title','description','origen','minimo','maximo','limite','creada','finalizada'))
							->joinLeft(array('b'=>$selectB), 'a.id=b.idRelacion',array('comentarios'=>new Zend_Db_Expr('IFNULL(comentarios, 0)')))
							->where('a.id =?',$idtarea);
		
		$selectC = $TareasTagDb->select()->setIntegrityCheck(false)
					->from(array('a'=>$TareasTagDb->__get('_name')),null)
					->joinleft(array('b'=>'cal_tags'),'b.id=a.idTags',array('tag'))
					->where('a.idtarea=?',$idtarea);
		
		$tagsResult = $TareasTagDb->fetchAll($selectC)->toArray();
		
// 		Application_Plugin_LogFirebug::log($select->__toString());
		
		$Tareas = array();
		$Tareas['list'] = $TareasDb->fetchRow($select)->toArray();
		$Tareas['count'] = count($Tareas['list']);
		$Tareas['list']['description'] = nl2br($Tareas['list']['description']);
		
		$s = new DateTime($Tareas['list']['creada']);
		$r = new DateTime($Tareas['list']['limite']);
		
		$Tareas['list']['creada'] = $s->format('Y/m/d H:i:s');
		$Tareas['list']['limite'] = $r->format('Y/m/d H:i:s');
		$Tareas['list']['tags'] = $tagsResult;
		//$Tareas['list']['tags'] = array(array('tag'=>'test'),array('tag'=>'fsdfsd'),array('tag'=>'test'),array('tag'=>'fdsfsd'),array('tag'=>'5435dsadfsdfs'),array('tag'=>'ssaasasas'),array('tag'=>'ssdssss'),array('tag'=>'5435dsadfsdfs'));
		
		
		return json_encode($Tareas);
	}
	
	
	/**
	 * Devuelve el estado de la tarea. Especificando el atributo '$class' como true devuelve la clase correspondiente
	 *
	 * @param array $tareas
	 */
	private function setTiming($tareas)
	{
		$i = isset($class)?1:0;
		$now = new DateTime();
	
		for($i=0;$i<count($tareas);$i++)
		{
		$end = new DateTime($tareas[$i]['limite']);
		$interval = $now->diff($end);
		// 			if()
				
		$tareas[$i]['class'] = $interval->format('%R%a');
		}
	
		return $tareas;
	}
	
	/**
	 * Sets the view field
	 * 
	 * @param $view Zend_View_Interface       	
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}