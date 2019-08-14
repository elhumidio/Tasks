<?php
class Application_Model_DbTable_CambioTurno_CambioTurno extends Zend_Db_Table {
	
	protected $_name = "t_cambio_turno";
	protected $_primary = array('ID');

	private $user = null;
	private $historial = null;
	private $cambioTurnoPapelera = null;
	private $historialPapelera = null;
	
	
	public function setInit($user){
	    $this->user = $user;
	    $this->historial = new Application_Model_DbTable_Generico_Historial();
	    $this->cambioTurnoPapelera = new Application_Model_DbTable_CambioTurno_CambioTurnoPapelera();
	    $this->historialPapelera = new Application_Model_DbTable_Generico_HistorialPapelera();
	}

	
	public function getEntradaId($id){
	    return  $this->find($id)->toArray();
    }
	
	public function insertaArray($registros){

	    for($i=0;$i<count($registros);$i++){
	        $id = $this->insert($registros[$i]);
	        $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Creada');
	        $registros[$i]['resultado'] = $id;
	    }

	    return $registros;
	    
	}
	
	public function getEntradasDiaGrupo($grupoUsuario, $fecha){

	    $sql = $this->select()->where('fecha=?', $fecha)->where('grupo=?', $grupoUsuario )->order(array('titulo', 'ID ASC'));
	    $result = $this->fetchAll($sql);
    
	    return $result->toArray();
	    
	}
	
// 	public function getEntradasDiaGrupoTurno($grupoUsuario, $fecha){
	     
	
	     
// 	    $sql = $this->select()
// 	    ->setIntegrityCheck(false)
// 	    ->from($this->_name)
// 	    //->join("t_turnos", "$this->_name.turno= t_turnos.ID", array('turno'=>'nombre'))
// 	    ->join("t_turnos", "$this->_name.turno= t_turnos.ID", array('turno'=>'nombre', 'turno_mostrar_franja'=>'mostrar_franja', 'turno_inicio'=>'inicio', 'turno_fin'=>'fin'))
// 	    ->where("$this->_name.grupo = ?", $grupoUsuario)
// 	    ->where("(($this->_name.fecha = ?", $fecha)
// 	    ->where("$this->_name.pendiente = 'N')")
// 	    ->orWhere("($this->_name.pendiente = 'Y'))")
// 	    ->order(array('t_turnos.orden', 'titulo', 'ID ASC'));
	     
// 	    $result = $this->fetchAll($sql);
// 	    return $result->toArray();
	
// 	}
	
	public function getEntradasDiaGrupoTurno($grupoUsuario, $fecha){
	    
        $sql = $this->select()
	        ->setIntegrityCheck(false)
	        ->from($this->_name)
	        ->join("t_turnos", "$this->_name.turno= t_turnos.ID", array('turno'=>'nombre', 'turno_mostrar_franja'=>'mostrar_franja', 'turno_inicio'=>'inicio', 'turno_fin'=>'fin'))
	        ->where("$this->_name.grupo = ?", $grupoUsuario)
	        ->where("$this->_name.fecha = ?", $fecha)
	        ->where("$this->_name.pendiente = 'N'")
	        ->order(array('t_turnos.orden', 'titulo', 'ID ASC'));
	        
        $result = $this->fetchAll($sql);
	    return $result->toArray();
	         
	}
	
	public function getEntradasPendiente($grupoUsuario){
	     
	    $sql = $this->select()
	    ->setIntegrityCheck(false)
	    ->from($this->_name)
	    ->join("t_turnos", "$this->_name.turno= t_turnos.ID", array('turno'=>'nombre', 'turno_mostrar_franja'=>'mostrar_franja', 'turno_inicio'=>'inicio', 'turno_fin'=>'fin'))
	    ->where("$this->_name.grupo = ?", $grupoUsuario)
	    ->Where("$this->_name.pendiente = 'Y'")
	    ->order(array('titulo', 'ID ASC'));
	     
	    $result = $this->fetchAll($sql);
	    return $result->toArray();
	
	}
	
	public function finalizaEntrada($id, $turno, $fecha){
	    
	    $res = $this->update(array('pendiente'=>'N', 'turno'=>$turno, 'fecha'=>$fecha, 'terminada'=>'Y'), array('ID = ?'=>$id));
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Finalizada');
	    return $res;
	    
	}
	
	public function pendienteEntrada($id){

	    $res = $this->update(array('pendiente'=>'Y'), array('ID = ?'=>$id));
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Pendiente');
	    return $res;
	     
	}

	public function criticoEntrada($id, $valor){
	
	    $res = $this->update(array('critico'=>$valor), array('ID = ?'=>$id));
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Critico-'.$valor);
	    return $res;
	
	}
	
	public function eliminaEntrada($id){

	    $res = $this->delete(array('ID = ?'=>$id));
	    return $res;
	
	}
	
	public function getEntradasDiaGrupoTitulo($grupoUsuario, $idTurno, $fecha, $titulo){

 	    $sql = $this->select()
            ->where('lower(titulo) = ?', strtolower($titulo))
     	    ->where('((fecha = ?', $fecha)
    	    ->where('turno = ?', $idTurno)
    	    ->where('grupo = ?', $grupoUsuario)
    	    ->where("pendiente = 'N')")
    	    ->orWhere("pendiente = 'Y')")
    	    ->order(array('ID DESC' ));
    	    
 	    $result = $this->fetchAll($sql);
 	    return $result->toArray();
    
	}
	
	public function procesaEditEntrada($id, $params){
	
	    $res = $this->update($params, array('ID = ?'=>$id));
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Editada');
	    return $res;
	
	}

	public function getEntradasBusqueda($textoBusqueda, $grupo){
	    
	    $busqueda = strtolower($textoBusqueda);
	    $busqueda = "%$busqueda%";
	    
	    $sql = $this->select()
	    ->where('(lower(titulo) like ?', $busqueda)
	    ->orWhere("lower(descripcion) like ?)", $busqueda)
	    ->where('grupo = ?', $grupo)
	    ->where('pendiente = ?', 'N')
 	    ->order(array('fecha ASC', 'titulo ASC', 'descripcion ASC'));
	    
	    $result = $this->fetchAll($sql);
	    return $result->toArray();
	    
	}
	
	public function muevePapeleraEntrada($id){
	
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Eliminada');
	    $entrada = $this->getEntradaId($id);
	
	    unset($entrada[0]['ID']);
	    $entrada[0]['usuario_borrado'] = $this->user->username;
	    $nuevoId = $this->cambioTurnoPapelera->insert($entrada[0]);
	     
	    $historialEntradas = $this->historial->getEntrada($id, 't_cambio_turno');
	     
	    $lista = array();
	    foreach($historialEntradas as $historialentrada){
	        unset($historialentrada['ID']);
	        $historialentrada['parent_id'] = $nuevoId;
	        $lista[] = $historialentrada;
	    }
	     
	    $this->historialPapelera->insertaArray($lista);
	
	    $this->historial->delete(array('parent_id = ?'=>$id));
	    $this->delete(array('ID = ?'=>$id));
	     
	    return true;
	
	}
	
	/**
	 * Recupera una entrada de la papelera
	 * @param string $id
	 */
	public function restauraPapeleraEntrada($id){
	   
	    $recuperado =  $this->cambioTurnoPapelera->getEntradaId($id);
	    $this->cambioTurnoPapelera->delete($id);
	    unset($recuperado[0]['fecha_borrado']);unset($recuperado[0]['usuario_borrado']);unset($recuperado[0]['ID']);
	    $newid = $this->insert($recuperado[0]);
	    $this->historial->add('t_cambio_turno', $newid, $this->user->username, $this->user->display_name, 'Recuperada');
	    $historialPapelera = $this->historialPapelera->getEntrada($id, 't_cambio_turno');
	    $ids = array();

	    foreach($historialPapelera as $hpapelera){
	        $this->historial->addWithDate('t_cambio_turno',$newid,$hpapelera['fecha'],$hpapelera['usuario_login'],$hpapelera['usuario'],$hpapelera['mensaje']);
	        $this->historialPapelera->delete($hpapelera['ID']);    
	    }
	    return true;
	}

	public function terminarEntrada($id){
	     
	    $entrada = $this->find($id)->toArray();
	    $terminada = $entrada[0]['terminada'] == 'Y' ? 'N' : 'Y';
	
	    $res = $this->update(array('terminada'=>$terminada), array('ID = ?'=>$id));
	    $this->historial->add('t_cambio_turno', $id, $this->user->username, $this->user->display_name, 'Terminada-'.$terminada);
	    return $res;
	     
	     
	}
	
	
}

?>