<?php
class Application_Model_DbTable_Easya_Intervenciones extends Zend_Db_Table {
	
	protected $_name = "small_intervenciones";
	protected $_primary = 'ID';
	
	protected function _setupDatabaseAdapter() {
		$this->_db = Zend_Registry::get ( 'easya' );
		parent::_setupDatabaseAdapter ();
	}
	
   /**
    * Get Interventions Not Treated In Task
    * @return array
    */
   public function GetInterventionsNotTreatedInTask()
   {
   	    $query = "  SELECT distinct * from small_intervenciones si
 					LEFT JOIN
 					small_intervenciones_task sit ON sit.id_intervencion = si.id
 					WHERE sit.id_intervencion is null 
 					AND si.fin_manual <> 1
   					AND now() <= si.fecha_fin";
            //file_put_contents("query_getInterventions.txt", $query);
   		$stmt = $this->_db->query($query);
   		$rows = $stmt->fetchAll();
   		if(count($rows) > 0)
   			return $rows;
   		else return false;
   		
   }


     /**
    * Updates All Intervention Fields
    */
   public function UpdateInterventionAllFields()
   {
   	
   	$eventos = new Application_Model_DbTable_Calendar_Eventos();
   	$rows = $eventos->GetInterventionEvents();
   	
   		
   	//select all events from cal_events which are from intervention type= EASY_INTERVENCION
   	try{
   		
   		$count=count($rows);
   		$interventionsList = array();
   		for($i = 0; $i < $count; $i++)
   		{
   			$params = json_decode($rows[$i]["params"],true);
   			$idIntervencion = $params["idIntervention"];
   			array_push($interventionsList,$idIntervencion);
   		}
   		
   		      $query = parent::select()->where('ID IN (?) ', $interventionsList);
   				$arrayevents= parent::fetchAll($query)->toArray();
   				 
   		
   		$idEvento = "";
   	
   		if(count($arrayevents) >0){
   			for($i = 0; $i < count($arrayevents);$i++)
   			{
   			//buscar en array eventos el idIntervencion
   				for($m = 0;$m < count($rows);$m++)
   				{
   				$params = json_decode($rows[$m]["params"],true);
   				if($arrayevents[$i]["ID"] == $params["idIntervention"])
   				{
   				$idEvento =(int)$rows[$m]["id"];
   				}
   				}
   					
   				//Update Intervencion Event
   					 
   					if($arrayevents != false)
   					{
   						$cis =	"";
   						//obtener lista de cis
   					
   						$arrayServidores = unserialize($arrayevents[$i]["data_servidor"]);
   						
   						for($x=0; $x < count($arrayServidores);$x++)
   						{
   							if($x==0)
   								$cis.=$arrayServidores[$x][0];
   							else $cis.=", ".$arrayServidores[$x][0];	
   						}
   						$historico = $arrayevents[$i]['fin_manual'] == 1 ? true : false;
   						
   						$tipoTarea = "Info_Intervencion";
   						$k= array('turno','centro','t_start','t_end','client', 'customer_affected', 'title', 'description','origen','OU_ID','creada',
   						'programacion','owner','params','group','type','refer');
   						$iparams = json_encode(array(
   						'idChange' => $arrayevents[$i]['ticket'], 'idTarea' => "Parent", 'Status'=> 'Pending',
   						'Service' => '', 'Environment' => '','tipo_intervencion'=>$arrayevents[$i]['tipo'],
   						'CI' => $cis
   						,'Coordinator'=>$arrayevents[$i]['tecnico'], 'idIntervention' => $arrayevents[$i]['ID'],
   						'INFO'=>true,'Intervention'=>true,'fin_manual'=>$arrayevents[$i]['fin_manual'],'historico'=>$historico));
   						$dataservidores = unserialize($arrayevents[$i]["data_servidor"]);
   						
   						$data = array(
	   						'title'			=>"Tipo: Reinicio - ".substr($arrayevents[$i]['observaciones'], 0, 70),
	   						'start'			=>$arrayevents[$i]['fecha_inicio'],
	   						'end'			=>$arrayevents[$i]['fecha_fin'],
	   						'description'	=>$arrayevents[$i]['observaciones'],
	   						'origen'		=>"Journal",
	   						'turno'			=>"morning" ,
	   						'refer' 		=>(is_null($arrayevents[$i]['ticket']))?'':$arrayevents[$i]['ticket'],
	   						'centro'		=>$arrayevents[$i]['entorno'],
	   						'cliente'		=>$arrayevents[$i]['cliente'],
	   						'group'			=>"EASY",
	   						'rgroup'		=>$arrayevents[$i]['tipo'],
	   						'type' 			=>"EASY_INTERVENCION" ,
	   						'params' =>(is_null($iparams))?'':$iparams,
	   						'customer_affected' =>$dataservidores[0][1],
	   						'open-close-ticket' => null,
	   						'status' => "Pending",
	   						'creada'=>  date("Y-m-d H:i:s"),
	   						'usuario' => $arrayevents[$i]['tecnico']
   						);
   						$EventsDb = new Application_Model_DbTable_Calendar_Eventos();
   						if(isset($idEvento) && $idEvento != "")
   						{
   							$where = array('id ?'=>$idEvento);
   							$EventsDb->update($data, 'id = '.$idEvento);
   						}
   						
   					}
   					}
   			}

   			}
   			catch(Exception $ex){
               return null;
   				//file_put_contents("exceptionverifyintervention.txt",$ex);
   			}
   }
   
   public function VerifyInterventionPassToHistoric()
   {
	$eventos = new Application_Model_DbTable_Calendar_Eventos();
	$rows = $eventos->GetInterventionEvents();
	//select all events from cal_events which are from intervention type= EASY_INTERVENCION
   	try{
   	//	file_put_contents("countfilas.txt",count($rows));
   		$count=count($rows);
   		for($i = 0; $i < $count; $i++)
   		{
   			$params = json_decode($rows[$i]["params"],true);
   		
		   		if($params["fin_manual"] == 0 || $params["historico"] = false)
		   		{
		   			if(isset($params["idIntervention"]))
		   			{
		   				//lo buscamos en la tabla small_intervenciones y actualizamos el valor de los parametros segun fin_manual y date_end
		   				$query2 = "SELECT * from small_intervenciones WHERE ID = ".$params["idIntervention"]. " AND now() > fecha_fin OR fin_manual = 1";
		   				$stmt = $this->_db->query($query2);
		   				$res = $stmt->fetchAll();
		   				if(count($res) > 0)
		   				{
		   					$params["fin_manual"] = $res[0]["fin_manual"];
		   					$params["historico"]  = true;
		   					$data["params"]       = json_encode($params);
		   					$where['id = ?'] = $rows[$i]['id'];
		   					$eventos->UpdateEvent($data,$where);
		   				}
		   			}
		   		
	   			}
   			}
   	}
   	catch(Exception $ex){
   		file_put_contents("exceptionverifyintervention.txt",$ex);
   	}
   	
   	
   }
   
   /**
    * Insert Intervention Task Control
    * @param array $dataintervencion
    */
   public function InsertInterventionTaskControl($dataintervencion)
   {
    if($dataintervencion["id_intervencion"] != "")
    {
      $this->_name            = "small_intervenciones_task";
      $this->_primary         = "id_intervencion";
      $data["id_intervencion"]    = $dataintervencion["id_intervencion"];
      $data["servidores"]     = $dataintervencion["servidores"];
      return parent::insert($data);
    }
    else return "NA";
   		
   }
	
	

	
}

?>