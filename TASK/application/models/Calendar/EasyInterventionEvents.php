<?php
class Application_Model_Calendar_EasyInterventionEvents
{
    
 public function   __construct(){
     
     $options = Zend_Registry::get ( 'configuracion.ini' );
     $this->urlportal = $options['urlportal'];	//'http://'.$_SERVER['HTTP_HOST'] no es válido porque en VMWZ422 está en http://10.49.162.33:880
     //$this->urlportalproyectos = str_replace("/task","/projects",$options['urlportal']);
	 $this->urlportal.'/';
     //$options = Zend_Registry::get ( 'configuracion.ini' );
     
      
 }
	 public function UpdateInterventionAllFields()
	 {
	 	try {
	 			$intdb  = new Application_Model_DbTable_Easya_Intervenciones();
	 			$intdb->UpdateInterventionAllFields();
	 	} catch (Exception $e) {
	 		file_put_contents("UpdateInterventionAllFields_exception.txt",$e);
	 	}
	 }
 
     /**
      * Get datos proyectos y tarea
      */
    private function GetInterventionData()
    {
	
    	$int = new Application_Model_DbTable_Easya_Intervenciones();
		$res =  $int->GetInterventionsNotTreatedInTask();
        return $res; 
        
    }
    
   
    
    
        /**
     * Create events from intervenciones
     */
	public function CreateEventsFromIntervenciones()
	{
		$smallInt = new Application_Model_DbTable_Easya_Intervenciones();
		$TareasDb = new Application_Model_DbTable_Calendar_Tareas();
		$EventsDb = new Application_Model_DbTable_Calendar_Eventos();
		$opciones = new Application_Model_DbTable_Calendar_EventosOpciones();
		
		$arrayevents = $this->GetInterventionData();
		//file_put_contents("arrayevents.txt",json_encode($arrayevents)."   ".count($arrayevents));
			if($arrayevents != false)
			{
				
				for($i = 0; $i < count($arrayevents); $i++)
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
			
				//Creacion de tarea
				$xxx = array();
				$xx = 1;
				$tipoTarea = "Info_Intervencion";
				$k= array('turno','centro','t_start','t_end','client', 'customer_affected', 'title', 'description','origen','OU_ID','creada',
						'programacion','owner','params','group','type','refer');
				$iparams = json_encode(array(
						'idChange' => $arrayevents[$i]['ticket'], 'idTarea' => "Parent", 'Status'=> 'Pending',
						'Service' => '', 'Environment' => '',
						'CI' => $cis
						,'Coordinator'=>$arrayevents[$i]['tecnico'], 'idIntervention' => $arrayevents[$i]['ID'],'tipo_intervencion'=>$arrayevents[$i]['tipo'],
						'INFO'=>true,'Intervention'=>true,'fin_manual'=>$arrayevents[$i]['fin_manual'],'historico'=>false));
				 
			
				
				$rs[0] = 'Morning';
				$rs[1] = 'BCN - 22@';
				$rs[2] = date("H:i", strtotime($arrayevents[$i]['fecha_inicio']));
				$rs[3] = date("H:i", strtotime($arrayevents[$i]['fecha_fin']));
				$rs[4] = '87'; // CTTI
				//$rs[5] = 'T-Systems Iberia/T-Systems Iberia';
				
				$rs[5] = 'T-SYSTEMS IBERIA/T-SYSTEMS IBERIA';
				$rs[6] =  "EASY INTERVENCIONES - ".substr($arrayevents[$i]['observaciones'], 0, 70);
				$rs[7] = "Tipo: Reinicio - ".$arrayevents[$i]['cliente']." - ".$arrayevents[$i]['observaciones'];
				$rs[8] = "Journal";
				$rs[9] = $arrayevents[$i]['tecnico'];
				$rs[10] = date("Y-m-d H:i:s");
				$rs[11] = (isset($xxx))?json_encode ($xxx):json_encode (array());
				$rs[12] = isset($arrayevents[$i]['tecnico']) ? $arrayevents[$i]['tecnico'] : "";
				$rs[13] = $iparams;
				$rs[14] = 'EASY_INTERVENCIONES';
				$rs[15] = 'INTERVENCIONES';
				$rs[16] = $arrayevents[$i]['ci'];
				
				$res = array_combine($k, $rs);
				
				$dat['id'] = $TareasDb->InsertTarea($res);
				
				
				//Creacion de evento
					$dataservidores = unserialize($arrayevents[$i]["data_servidor"]);
						
					$data = array('id'=>null,
						'title'			=>"Tipo: Reinicio - ".substr($arrayevents[$i]['observaciones'], 0, 70),
						'start'			=>$arrayevents[$i]['fecha_inicio'],
						'end'			=>$arrayevents[$i]['fecha_fin'],
						'description'	=>$arrayevents[$i]['observaciones'],
						'origen'		=>"Journal",
						'idtarea'		=>$dat['id'],
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
					$id = $EventsDb->InsertEvent($data);
					$opciones->SetEventValue($id,"className","Journal_Pending");
					//insert en tabla control
					$dataintervencion = array();
					$dataintervencion["id_intervencion"] 	= $arrayevents[$i]['ID'];
					$dataintervencion["servidores"] 		= $arrayevents[$i]["data_servidor"];
					$smallInt->InsertInterventionTaskControl($dataintervencion);
			}
			}

		
	}

    
    
}