<?php
class Application_Model_CambioTurno_CambioTurno{

    private $user;
    private $dbModel;
    
    function __construct($user){
        $this->user = $user;
        $this->dbModel = new Application_Model_DbTable_CambioTurno_CambioTurno();
        $this->dbModel->setInit($user);
    }
 
    public function procesaEntradas($params){
        
        $entradas = $this->createEntradas($params);
        $res = $this->dbModel->insertaArray($entradas);
                
    }
    
    public function getIdTurno($grupo){
        
        $dbModelTurnos = new Application_Model_DbTable_Generico_Turnos();
        $turnos = $dbModelTurnos->getTurnosGrupo($grupo);
        
        $ahora = strtotime(date("Y-m-d H:i:00",time()));
        
        for($i=0;$i<count($turnos);$i++){
            $tmp = $turnos[$i];
            
            $min = strtotime(date('Y-m-d').' '.$tmp['inicio']);
            $max = strtotime(date('Y-m-d').' '.$tmp['fin']);

            if($ahora>=$min && $ahora <$max){
                return $tmp['ID'];
            }
        }
        
        return false;
    }
    
    
    private function createEntradas($params){
        
        $titulo = $params['titulo'];
        $descripciones = $params['descripcion'];
        $pendientes = $params['pendiente'];
        $grupo = $params['grupo'];
        $critico = $params['critico'];
        $fecha = date('Y-m-d');
        $idTurno = $this->getIdTurno($grupo);
        
        $entradas = array();
        
        for($i=0;$i<count($descripciones);$i++){
            
            if ($descripciones[$i]){
                $pendiente = $pendientes[$i] == '1'?'Y':'N';
                $critico = $critico[$i] == '1'?'Y':'N';
                $entradas[] = array(
                    'titulo'=>$titulo,
                    'descripcion'=>$descripciones[$i],
                    'usuario'=>$this->user->username,
                    'grupo'=>$grupo,
                    'fecha'=>$fecha,
                    'pendiente'=>$pendiente,
                    'turno'=> $idTurno,
                    'critico'=>$critico
                );
                
            }
        }



        return $entradas;
        
    }

    public function getEntradasDia($grupoUsuario, $fecha, $busqueda= null){
    
        $entradas = $this->getEntradas($grupoUsuario, $fecha, $busqueda);
        $tabla = $this->getDiv($entradas, $busqueda);
        
        return $tabla;
        
    }
    
    private function getEntradas($grupoUsuario, $fecha){

        $pendientes = $this->dbModel->getEntradasPendiente($grupoUsuario);
        $entradas = $this->dbModel->getEntradasDiaGrupoTurno($grupoUsuario, $fecha);
        return array_merge($pendientes, $entradas);
        
    }
    
    private function separaPorPendientes($entradas){
        
        $entradasDia = array();
        $entradasPendiente = array();
        
        foreach($entradas as $entrada){
            if($entrada['pendiente'] == 'Y'){
                $entradasPendiente[] = $entrada;
            } else {
                $entradasDia[] = $entrada;
            }
        }
        
        return array('dia'=>$entradasDia, 'pendiente'=>$entradasPendiente);
        
    }
    
    private function getDiv($entradas, $busqueda= null){

        $entradasSeparadas = $this->separaPorPendientes($entradas);
    
        $entradasDia = $entradasSeparadas['dia'];
        $entradasPendiente = $entradasSeparadas['pendiente'];
    
        $salida = null;
        $tituloAnterior = false;
    
    
          foreach($entradasPendiente as $entrada){
    
            $id = $entrada['ID'];
            
            $titulo = htmlspecialchars($entrada['titulo']);
            $descripcion = htmlspecialchars($entrada['descripcion']);
            $pendiente = $entrada['pendiente'];
            $critico = $entrada['critico'];

            $titulo = $this->resaltaTexto($busqueda, $titulo);
            $descripcion = $this->resaltaTexto($busqueda, $descripcion);
            
            if (!$tituloAnterior) $salida .= "<div class=\"grupo_cambio_turno\"><span class=\"cab_turno\">Pendientes</span><div class=\"grupo_cambio_titulo\">";
            if ($tituloAnterior != $titulo) $salida .= " <span class=\"cab_titulo\">$titulo</span><hr>";
            
            $valueCritico = $critico == 'N' ? "" : "<img id=\"img_critico\" src=\"/img/icon32/fire.png\" height=\"15\" alt=\"Historial\" title=\"Critico\">"; 

            
            $salida .= "
            <div  class=\"elem_cambio_turno context-pendiente\"  id =\"cab_$id\" \">";
            if($valueCritico != "")
            $salida .= "<div id =\"det_$id\" class=\"det criticoclass\">";
            else
                $salida .= "<div id =\"det_$id\" class=\"det\">";
            $salida .="<img class=\"bolita\" src=\"/img/icon/b_blue.png\" height=\"10\" alt=\"Descripcion\" title=\"Descripcion\" >$descripcion
            </div>
            <div id =\"det_der_$id\" class=\"det_der\">
            $valueCritico
            <img id=\"img_historial\" src=\"/img/icon32/clock_history_frame.png\" height=\"15\" alt=\"Historial\" title=\"Historial\" onclick=\"cambioTurno_muestraHistorial($id);\">
            </div>
            </div>";

                        
          $tituloAnterior = $titulo;
    
          }
        
        if ($tituloAnterior) $salida .="</div></div>";
    
    
        $turnoAnterior = false;
        $tituloAnterior = false;
    
        foreach($entradasDia as $entrada){
    
//             $turno = $entrada['turno'];
            
            $turno_mostrar_franja = $entrada['turno_mostrar_franja'];
            
            if ($turno_mostrar_franja){
                $turno_inicio = date('H:i', strtotime($entrada['turno_inicio']));
                $turno_fin = date('H:i', strtotime($entrada['turno_fin']));
                $turno = $entrada['turno'].' ('.$turno_inicio.' a '.$turno_fin.')';
            
            } else {
                $turno = $entrada['turno'];
            
            }
            
            
            $id = $entrada['ID'];
            $titulo = htmlspecialchars($entrada['titulo']);
            $descripcion = htmlspecialchars($entrada['descripcion']);
            $pendiente = $entrada['pendiente'];
            $critico = $entrada['critico'];
            $terminada = $entrada['terminada'];
            
            $valueCritico = $critico == 'N' ? "" : "<img id=\"img_critico\" src=\"/img/icon32/fire.png\" height=\"15\" alt=\"Historial\" title=\"Critico\">";
            //$valueTerminada = $terminada == 'N' ? "" : "<img id=\"img_terminada\" src=\"/img/icon/tick.png\" height=\"15\" alt=\"Terminada\" title=\"Terminada\">";
            
            $titulo = $this->resaltaTexto($busqueda, $titulo);
            $descripcion = $this->resaltaTexto($busqueda, $descripcion);
            
            $iconoIzquierda = $terminada == 'N' ?
                "<img class=\"bolita\" src=\"/img/icon/b_blue.png\" height=\"10\" alt=\"Descripcion\" title=\"Descripcion\" >" :
                "<img id=\"img_terminada\" src=\"/img/icon/tick.png\" height=\"15\" alt=\"Terminada\" title=\"Terminada\">";
            
            
            
            if (!$turnoAnterior) {
                $salida .= "<div class=\"grupo_cambio_turno\"><span class=\"cab_turno\">$turno</span>";
                $salida .= "<div class=\"grupo_cambio_titulo\"><span class=\"cab_titulo\">$titulo</span><hr>";
            }
             
            if ($turnoAnterior && $turnoAnterior != $turno) {
                $salida .= "</div></div>";
                $salida .= "<div class=\"grupo_cambio_turno\"><span class=\"cab_turno\">$turno</span>";
                $salida .= "<div class=\"grupo_cambio_titulo\"><span class=\"cab_titulo\">$titulo</span><hr>";
    
            } else if ($tituloAnterior && $tituloAnterior != $titulo){
                $salida .= "<span class=\"cab_titulo\">$titulo</span><hr>";
            }
            if($valueCritico != "")
                $salida .= "
                <div  class=\"elem_cambio_turno context\"  id =\"cab_$id\" \"><div id =\"det_$id\" class=\"det criticoclass\">$iconoIzquierda$descripcion</div><div id =\"det_der_$id\" class=\"det_der\">";
            else
                $salida .= "
                <div  class=\"elem_cambio_turno context\"  id =\"cab_$id\" \"><div id =\"det_$id\" class=\"det\">$iconoIzquierda$descripcion</div><div id =\"det_der_$id\" class=\"det_der\">";
            $salida .=  $valueCritico;
            $salida .= " <img id=\"img_historial\" src=\"/img/icon32/clock_history_frame.png\" height=\"15\" alt=\"Historial Entrada\" title=\"Historial Entrada\" onclick=\"cambioTurno_muestraHistorial($id);\">
             </div>
            </div>";
    
            $turnoAnterior = $turno;
            $tituloAnterior = $titulo;
    
        }
         
        if ($turnoAnterior) $salida .="</div></div>";
    
        return $salida;
    
    }

    public function finalizaEntrada($id, $fecha){
        
        $entrada = $this->dbModel->getEntradaId($id);
        
        if (count($entrada)>0){
            $idTurno = $this->getIdTurno($entrada[0]['grupo']);
            return $this->dbModel->finalizaEntrada($id, $idTurno, $fecha);
            
        } else {
          return false;
          
        }
        
    }

    public function pendienteEntrada($id){
    
        return $this->dbModel->pendienteEntrada($id);
    
    }

    public function criticoEntrada($id){
    
        $entrada = $this->dbModel->getEntradaId($id);
        $valor = $entrada[0]['critico'] == 'Y' ? 'N' : 'Y'; 
        return $this->dbModel->criticoEntrada($id, $valor);
    
    }
    
    
    
    
    public function eliminaEntrada($id){
    
        //return $this->dbModel->eliminaEntrada($id);
        return $this->dbModel->muevePapeleraEntrada($id);
    
    }
    
    
    public function recuperaEntrada($id)
    {
        return $this->dbModel->restauraPapeleraEntrada($id);
        
    }

    public function getHistorialEntrada($id){
        
        $entrada = $this->dbModel->getEntradaId($id);
        
        $dbHistorial = new Application_Model_DbTable_Generico_Historial();
        $historial = $dbHistorial->getEntrada($id, 't_cambio_turno');
        
        return array('entrada'=>$entrada,  'historial'=>$historial);
    }

    public function getEntradaEdicion($params){

        $entrada = $this->dbModel->getEntradaId($params['id'])[0];
        
        $dbModelTurnos = new Application_Model_DbTable_Generico_Turnos();
        $turnos = $dbModelTurnos->getTurnosGrupo($entrada['grupo']);
        
        return array('entrada'=>$entrada, 'turnos'=>$turnos); 

    }

    public function procesaEditEntrada($params){
        
        return $this->dbModel->procesaEditEntrada($params['id'], $params['params']);
        
    }
    
    public function getEntradasBusqueda($textoBusqueda, $grupo){
        return $this->dbModel->getEntradasBusqueda($textoBusqueda, $grupo);
    }
    

    public function resaltaTexto($palabra, $texto) {
    
        $aux = $reemp = str_ireplace($palabra,'%s',$texto);
    
        $veces = substr_count($reemp,'%s');
        //die(var_dump($reemp, $aux, $veces));
    
        if($veces==0)return $texto;
        $palabras_originales=array();
    
//         '<span style="background-color:yellow;">'.$busqueda.'</span>
        for($i=0;$i<$veces;$i++){
            $palabras_originales[]='<span style="background-color:yellow;">'.substr($texto,strpos($aux,'%s'),strlen($palabra)).'</span>';
            $aux=substr($aux,0,strpos($aux,'%s')).$palabra.substr($aux,strlen(substr($aux,0,strpos($aux,'%s')))+2);
        }
    
        return vsprintf($reemp,$palabras_originales);
    
    }
    
    public function getPapelera(){
        $papeleraDb = new Application_Model_DbTable_CambioTurno_CambioTurnoPapelera();
        $papelera = $papeleraDb->get();
        return $papelera;
    }    
    
    
    
    
    public function getHistorialEntradaBorrada($id){
    
        $dbPapelera = new  Application_Model_DbTable_CambioTurno_CambioTurnoPapelera();
        $entrada = $dbPapelera->getEntradaId($id);
    
        $dbHistorial = new Application_Model_DbTable_Generico_HistorialPapelera();
        $historial = $dbHistorial->getEntrada($id, 't_cambio_turno');
    
        return array('entrada'=>$entrada,  'historial'=>$historial);
    }

    public function cambiaTerminarEntrada($id){
    
        return $this->dbModel->terminarEntrada($id);
    }
    
    /**
     * Obtiene los titulos
     */
    public function getTitulos()
    {
        $titulos = new Application_Model_DbTable_CambioTurno_CambioTurnoTitulos();
        $rtn = $titulos->get();
        return $rtn;
        
    }
    
    /**
     * Añade un título
     * @param string $titulo
     */
    public function addTitulo($titulo)
    {
        $m = new Application_Model_DbTable_CambioTurno_CambioTurnoTitulos();
        $m->addTitulo($titulo);
    }
    
    
    /**
     * 
     * @param string $id
     */
    public function deleteTitulo($id)
    {
        $m = new Application_Model_DbTable_CambioTurno_CambioTurnoTitulos();
        $m->deleteTitulo($id);
    }
    
    
    /**
     * Update titulo
     * @param string $id
     * @param string $titulo
     */
    public function updateTitulo($id,$titulo)
    {
        $m = new Application_Model_DbTable_CambioTurno_CambioTurnoTitulos();
        $m->updateTitulo($id,$titulo);
    }
    
    
    
    
    
    
}


?>
