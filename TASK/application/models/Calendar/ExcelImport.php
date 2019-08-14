<?php
/**
 * 
 * @author Toni Ibáñez Justicia - antonio.ibanez@t-systems.com
 * @version 1.0 - 11/05/2015
 *
 */
error_reporting(E_ALL);
set_time_limit(0);
class Application_Model_Calendar_ExcelImport
{
    
    private $systemLog;
    private $error;
    /**
     * Variable que contiene un objeto con los datos del usuario logueado
     * @var object
     */
    private $userData;
    
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
    
    private $Notificaciones;
    
    
    public function __construct($params=array(),$inputFileName)
    {
        $this->params = $params;
        
        $storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
        $namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
        $this->userData = $storage->read($namespace);
        
        $this->eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $this->eLog->__set('username',$this->userData->username);
        $this->eLog->__set('OU_ID',$this->userData->OU_ID);
        $this->systemLog = new Application_Plugin_SystemLog();
        $this->systemLog->setPrefijo(get_class ());
        $this->tLog = new Application_Model_DbTable_Calendar_TareasLog();
        $this->tLog->__set('username',$this->userData->username);
        $this->tLog->__set('OU_ID',$this->userData->OU_ID);
        $this->EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $this->UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
        $this->EventsOptDb = new Application_Model_DbTable_Calendar_EventosOpciones();
        

        $this->TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $this->UsusarioTareasDb = new Application_Model_DbTable_Calendar_UsersTareas();
        $this->TareasOptDb = new Application_Model_DbTable_Calendar_TareasOpciones();
        
        $this->inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $this->inputFileName = $inputFileName;
        $this->objReader = PHPExcel_IOFactory::createReader($this->inputFileType);
        $this->objPHPExcel = null;
        
    }
    
     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function GetExcelEvents()
    {
        $sheetData = array();
        $vr = 0;
        //Application_Model_chunkReadFilter
        $sheets = self::getWorkSheetsInfo();
        try {
                $this->objPHPExcel = $this->objReader->load($this->inputFileName);
                foreach ($sheets as $sheet) {
                    if(strlen($sheet['worksheetName'])==2){
                        $this->objPHPExcel->setActiveSheetIndex($vr);
                       $dateSheet = DateTime::createFromFormat('m-d-y H:i:s', implode(self::getArray('J3:J3')).'00:00:00');
                       
                       if(is_object($dateSheet))
                       {
                            $type = str_replace(" CoP","",implode(self::getArray('A1:A1')));
                            $sheetDat[$vr]['Date'] = date_format($dateSheet,'Y-m-d'); 
                            $sheetDat[$vr]['Columns'] = self::getArray('A5:K5');
                            $temp_array = self::getArray('A6:G'.$sheet['totalRows'],'rows');      
                            foreach ($temp_array as $da) {
                                if(array_filter($da, 'strlen'))
                                {
                                    $k= array('turno','centro','start','end','group','cliente','description','title','origen');
                                    $di = self::GetTime($dateSheet,$da[2],$da[3]);
                                    $da[2] = $di['start'];
                                    $da[3] = $di['end'];
                                    $da[7] = mb_substr($da[6],0,80);
                                    $da[7]  = mb_check_encoding($da[7], 'UTF-8') ? $da[7] : utf8_encode($da[7]);
                                    $da[8] = $type;
                                    $id = $this->EventsDb->InsertEvent(array_combine($k, $da));
                                    $this->UsusarioDb->InsertData(array('idEvento'=>$id,'username'=>$da[0],'OU_ID'=>$this->userData->OU_ID));
                                    $this->EventsOptDb->SetEventValue($id, 'className', $type.'_Pending');
                                    
                                    //$da[8] = $type;
                                     //$sheetDat[$vr]['Data'][] = array_combine($k, $da);
                                    //$sheetDat[$vr]['Data'][] = $da;    
                                }
                                
                                
                            }
                       }
                        ++$vr;
                    }
                }
        } catch(PHPExcel_Reader_Exception $e) {
            $this->systemLog->setLog('Error al cargar el archivo');
            $this->systemLog->setLog($e->getMessage());
            return array('error'=>$e->getMessage());
        }
        //$chunkFilter = new Application_Model_chunkReadFilter(1,320,range('A','K'));
        //$this->objReader->setReadFilter($chunkFilter);
        return $sheetDat;
    }

     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function GetExcelTemplates()
    {
        
       
        $sheetData = array();
        $vr = 0;
        //Application_Model_chunkReadFilter
        $sheets = self::getWorkSheetsInfo();
        try {
                $this->objPHPExcel = $this->objReader->load($this->inputFileName);
                $type = 'Checklist';
                foreach ($sheets as $sheet) {
                    if($sheet['worksheetName'] === 'Tareas Principal'){
                        $type = 'Checklist';
                        $this->objPHPExcel->setActiveSheetIndex($vr);
                            $sheetDat[$vr]['Columns'] = self::getArray('A1:O1');
                            $temp_array = self::getArray('A2:P'.$sheet['totalRows'],'rows');   

                            foreach ($temp_array as $da) {
                                
                                if(array_filter($da, 'strlen'))
                                {
                                    $xxx = array();
                                    $da_arr = array();
                                    $xx = 1;
                                    $k= array('turno','centro','t_start','t_end','group','client', 'title', 'description','origen','OU_ID','creada','programacion','owner', 'open-close-ticket', 'minutes-close-ticket');
                                    //$di = self::GetTime($dateSheet,$da[2],$da[3]);
                             
                                    for ($x = 8; $x <= 14; $x++) {
                                        if($da[$x] =='x'){
                                            $xxx['fr_tmp']['P1D'][] = $xx; }
                                        $xx++;
									}
                                    $da[8] = $type;//Checklist
                                    $da[9] = $this->userData->OU_ID;//OU_ID
                                    $da[10] = date("Y-m-d H:i:s");//Fecha creacion
                                    $da[11] = (isset($xxx))?json_encode ($xxx):json_encode (array());//programacion creada
                                    $da[12] = $this->userData->username;//Owner
                                    //unset($da[11]);
                                    unset($da[13]);
                                    unset($da[14]);    
                                    
                                    if ($da[15]){
                                        $da[13] = 1;
                                        $da[14] = $da[15];
                                    } else {
                                        $da[13] = null;
                                        $da[14] = null;
                                    }
                                    unset($da[15]);
                                    
                                    
                                    
                                    $res['data'] = array_combine($k, $da);
                                    $res['id'] = $this->TareasDb->InsertTarea($res['data']);
                                    $this->UsusarioTareasDb->AsignarUsuario($res['id'], $da[0], $da[9]);
                                    $this->TareasOptDb->SetTareasValue($res['id'], 'className', $type.'_Pending');
                                    unset($res['data']['creada']);
                                    
                                    
                                     // Toni, como crear los eventos recurrentes, recordar que hay que forzar inicio de semana a Lunes y no a domingo.
                                    if(isset($xxx['fr_tmp']['P1D']))
                                    {
                                        $start = new DateTime( "now" );
                                        $end = new DateTime( "now +1 month" );
                                        $interval = new DateInterval('P1D');
                                        $period = new DatePeriod($start, $interval, $end);
                                        //$mydays = array('1', '3', '5');
                                        foreach($period as $date) {
                                            if(in_array($date->format('N'), $xxx['fr_tmp']['P1D'])) {
                                                $da_arr[] = $date->format( "Y/m/d\n" );
                                            //do something for Monday, Wednesday and Friday
                                                }
                                            }
                                        $res['date'] = $da_arr;
                                    }
                                    $result[] = $res;
                                }
                            }
                       }
                        ++$vr;
                }
        return $result;
        } catch(PHPExcel_Reader_Exception $e) {
            $this->systemLog->setLog('Error al cargar el archivo');
            $this->systemLog->setLog($e->getMessage());
            return array('error'=>$e->getMessage());
            
        }
        //$chunkFilter = new Application_Model_chunkReadFilter(1,320,range('A','K'));
        //$this->objReader->setReadFilter($chunkFilter);
        
    }


     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function GetExcelJTemplates()
    {
        
        $rowDateErrorNumber = 2;
        $wrongDates = false;
       
        $sheetData = array();
        $vr = 0;
        
        //Application_Model_chunkReadFilter
        $sheets = self::getWorkSheetsInfo();
      
        try {
                $this->objPHPExcel = $this->objReader->load($this->inputFileName);
                $type = 'Journal';
              
                
                
                /* 
                 * Guardamos el valor de template_version en un custom Property
                 * del mismo documento de excel para asegurarnos que no se utilice una version diferente de documento
                 * si usamos una version diferente devolvemos un array con codigo de error para indicar que el documento
                 * es incorrecto, e indicamos que se use la plantilla de la pagina.
                 * Para acceder al custom propierties, icono de office, menu preparar, Propiedades, propiedades avanzadas
                 * */
                if($this->objPHPExcel->getProperties()->getCustomPropertyValue('template_version') == 'Template_journal_v11')
                {
                    //die('Ok');
                    foreach ($sheets as $sheet) {
                    if($sheet['worksheetName'] === 'Tareas Principal'){
                        $type = 'Journal';
                        $this->objPHPExcel->setActiveSheetIndex($vr);
                        
                        $mergeCells = $this->getMergeCells();
                        if ($mergeCells)return array('error'=>'There are merge cells: '.$mergeCells);
                        
                        
                        $sheetDat[$vr]['Columns'] = self::getArray('A1:O1');
                            $temp_array = self::getArray('A2:O'.$sheet['totalRows'],'rows');   
							 // Verificamos si no hay fechas erroneas en el excel
                        foreach ($temp_array as $da) {
                            
                            if (strtotime($da[2]) > strtotime($da[3])) {
                                $wrongDates = true;
                                break;
                            }
                            ++ $rowDateErrorNumber;
                        
                        }

						if (! $wrongDates) {
                            foreach ($temp_array as $da) {
                                
                                if(array_filter($da, 'strlen'))
                                {
                                   //print_r($da);
                                    $xxx = array();
                                    $da_arr = array();
                                    $xx = 1;
                                    $k= array('turno','centro','t_start','t_end','client', 'customer_affected', 'title', 'description','origen','OU_ID','creada','programacion','owner','params','group','type');
                                    $i = json_encode(array(
                                                            'idChange' => $da[7], 'idTarea' => 'Parent', 'Status'=> (is_null($da[6]))?'Info':$da[6],
                                                            'Service' => $da[8], 'Environment' => $da[9], 'Coordinator' =>$da[10],
                                                            'Approval Status' => '', 'CBI' => $da[11], 'CI' => $da[14]
                                                            ));
                                                            
                                     $res['idChange'] = $da[7];                                                         
                    
                                    
                                    $da_arr =array(date("Y/m/d\n", strtotime($da[2])));
                                    $da[2] = date("H:i", strtotime($da[2]));
                                    $da[3] = date("H:i", strtotime($da[3]));
                                    $da[7] = $da[13];
                                    $da[13] = $i;
                                    $da[14] = 'MULTI';
                                    $da[15] = 'SM9';
                                    $da[6] = $da[12];          
                                    $da[8] = $type;//Checklist
                                    $da[9] = $this->userData->OU_ID;
                                    $da[10] = date("Y-m-d H:i:s");
                                    $da[11] = (isset($xxx))?json_encode ($xxx):json_encode (array());
                                    $da[12] = $this->userData->username;
                 
                                    $res['data'] = array_combine($k, $da);
                                    $res['id'] = $this->TareasDb->InsertTarea($res['data']);
                                    
                                    
                                    $this->UsusarioTareasDb->AsignarUsuario($res['id'], $da[0], $da[9]);
                                    $this->TareasOptDb->SetTareasValue($res['id'], 'className', $type.'_Pending');
                                    unset($res['data']['creada']);
                                     // Toni, como crear los eventos recurrentes, recordar que hay que forzar inicio de semana a Lunes y no a domingo.
                                    $res['date'] = $da_arr;
                                    $result[] = $res;
                                }
                            }
						}
						else {
								return array(
                                    'error' => 'Error in row ' .
                                             $rowDateErrorNumber .
                                             '.Start date bigger than End date - Please verify excel file and try to load it again.');
							}
						
                       }
                        ++$vr;
                    }
                }

                elseif($this->objPHPExcel->getProperties()->getCustomPropertyValue('template_version') == 'Template_journal_ctti_v13')
                {
                    
					$eventos =  new Application_Model_DbTable_Calendar_Eventos();
                    $mergedCells = false;

                    foreach ($sheets as $sheet) {
                    if($sheet['worksheetName'] === 'Tareas Principal'){
                        $type = 'Journal';
                        $this->objPHPExcel->setActiveSheetIndex($vr);
                        
                        $mergeCells = $this->getMergeCells();
                        if ($mergeCells)
                            //return array('error'=>'There are merge cells: '.$mergeCells);
                            $mergedCells = true;
                      
							$sheetDat[$vr]['Columns'] = self::getArray('A1:O1');
                            $temp_array = self::getArray('A2:O'.$sheet['totalRows'],'rows');   
								              // Verificamos si no hay fechas erroneas en el excel
										  
							foreach ($temp_array as $da) {
                   
								if (strtotime($da[6]) > strtotime($da[7])) {
									$wrongDates = true;
									break;
								}
								++ $rowDateErrorNumber;
                
							}
							
                            foreach ($temp_array as $da) {
                                if($da[0] != null)
                                $check =$eventos->getEventoByIdChange($da[0]);
                                else continue;
                               // if($check)
                                 //   continue;
                                $row = $da;
                                $isInfo = false;
								$critical_services = $da[13];
								$copSplited = explode('~', $da[12]);
								$counter = count($copSplited);
								
								if(count($copSplited) >= 1)
								{
								    $counter = -1;
								    $isInfo = false;
								}
							    if(json_encode($da[12]) == "null")
								{
								
								    $counter = 0;
								    $isInfo = true;
								
								}

								    if(count($copSplited)> 0)
								    {
								        
										$idTarea = "";
			            
											$parent = 0;
					                            
								            for($len = $counter; $len < count($copSplited); $len ++)
								            {   
								                $taskText = $counter != -1 ? $copSplited[$len] : "";
												$res = self::getDataFromExcel($row,$len,$parent,$critical_services,$copSplited[$len],$check,$isInfo);
												$parent ++;
								           
								            if($len == -1){
								                $idTarea = $res['id'];
								            }
								            else{
								                $res['id'] = $idTarea;
								            }
								                
								            
								                if($len > -1)
								                {
        								                if (strtotime($da[6]) > strtotime($da[7]))
        								                $res['error'] = "Dates with error in this row (start bigger than end)";
        								                if($mergedCells)
        								                    $res['errormerged'] = 'There are merge cells: '.$mergeCells;
								                }
								            
								                $result[] = $res;
								            
								            }
					
									}	
						  
                            }
						
                       }
                        ++$vr;
                    }
                    
                } else {
                    file_put_contents("templateexcel.txt",$this->objPHPExcel->getProperties()->getCustomPropertyValue('template_version'));
                    return array('error'=>'Template out of date. Use templates donwload from this page');
                    
                }
				//TODO ELIMINAMOS FICHERO EXCEL
			 //file_put_contents("pathexcel.txt",$this->inputFileName);
            return $result;
        
        } catch(PHPExcel_Reader_Exception $e) {
			file_put_contents("exceptionborrandoarchivo.txt",$e);
            $this->systemLog->setLog('Error al cargar el archivo');
            $this->systemLog->setLog($e->getMessage());
            return array('error'=>$e->getMessage());
        }
        //$chunkFilter = new Application_Model_chunkReadFilter(1,320,range('A','K'));
        //$this->objReader->setReadFilter($chunkFilter);
        
    }
	

	
	/**
    * Funcion que obtiene y ordena los datos del excel Journal
    * @param string $da array con los datos de la fila del excel
	* @param int $len, posicion.
    * @return array $result 
    */  
	public function getDataFromExcel($da,$len,$parent,$critical_services,$taskText,$check,$isInfo)
	{
	try{
		
	    if (strlen(implode($da)) > 0)
	    {
									
									$xxx = array();
									$xx = 1;
									$tipoTarea = "";
									$tipoTarea = $parent == 0 ? "Parent" : preg_replace('/\s+/', '', $da[0])."_". ($len +1);
									$Significant = $da[14] == "Yes" ? "SIGNIFICANT" : "MINOR";
                                    $k= array('turno','centro','t_start','t_end','client', 'customer_affected', 'title', 'description','origen','OU_ID','creada','programacion','owner','params','group','type','refer');
                                      $i = json_encode(array(
                                            'idChange' => /*trim($da[0])*/ preg_replace('/\s+/', '', $da[0]), 'idTarea' => $tipoTarea, 
                                            'Status'=> (is_null($da[5]))?'Info':$da[5],
                                            'Service' => $da[2], 
                                            'Environment' => $da[3],
                                            'Approval Status' => '', 
                                            'CI' => $da[4], 
                                            'Downtime?' => $da[8],
                                            'Affected Groups' => $da[9], 
                                            'Petitioner' => $da[10], 
                                            'SDM' => $da[11],
                                            'critical_services'=>$critical_services,
                                            'CBI'=>$Significant,
                                            'INFO' => $isInfo
                                              
                                    ));
															
                                    $da_arr[] = array(date("Y/m/d", strtotime($da[6])));     
						            $da_arrend[] = array(date("Y/m/d", strtotime($da[7])));
                                     $res['idChange'] = $da[0];                                                         
                                    
                                    $rs[0] =    'Morning';
                                    $rs[1] =    'BCN - 22@';
                                    $rs[2] =    date("H:i", strtotime($da[6]));
                                    $rs[3] =    date("H:i", strtotime($da[7]));
                                    $rs[4] =    '62'; // CTTI
                                    $rs[5] =    'CTTI/CTTI';
									$rs[6] =    $taskText == "" ? $da[1]:$taskText;
                                    $rs[7] =    $taskText != "" ? $taskText : $da[1];
                                    $rs[8] =    "Journal";
                                    $rs[9] =    $this->userData->OU_ID;
                                    $rs[10] =   date("Y-m-d H:i:s");
                                    $rs[11] =   (isset($xxx))?json_encode ($xxx):json_encode (array());
                                    $rs[12] =   $this->userData->username;
                                    $rs[13] =   $i;
                                    $rs[14] =   'CTTI';
                                    $rs[15] =   'CTTI'; 
									$rs[16] =  /*trim($da[0]);*/preg_replace('/\s+/', '', $da[0]);
										
                                    $res['data'] = array_combine($k, $rs);
									if($parent == 0){
									    $res['parent'] = "parent";
									}
								   $res['dateend'] = $da_arrend;      
                                   $res['date'] = $da_arr;
								   $res['source'] = "JournalExcel";
								   $res['existing'] = $check ? "true" : "false";
								    
                                }
								
								return $res;
	}
	catch(Zend_Exception $e)
	{
		
		file_put_contents("exc1.txt" , "Caught exception: " . get_class($e) . "\n");
    
		file_put_contents("exc2.txt" , "Message: " . $e->getMessage());
	}
		
		
	}
   
     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function insertData()
    {
        $result = array();
        return $result;
    }

    /**
    * Funcion para calcular la fecha de inicio y fin de tareas
    * @param object $date objecto Datetime con el valor del dia
    * @param string $start string con la hora de inicio
    * @param string $end string con la hora de inicio
    * @return array $result array con una key para el inicio y otra para el fin
    */    
    public function GetTime($date,$start,$end)
    {
        $res['start'] = clone $res['end'] = clone $date;
        $result = array();
        foreach ($res as $key => $value) {
            if($end < $start) $value = $value ->modify('+1 day');
            $value->modify($$key);
            $result[$key] = date_format($value,'Y-m-d H:i');
         }
        return $result;
    }
    
    /**
    * Devuelve array con el rango seleccionado.
    * @param string $range Devuelve array con las cells seleccionadas
    * @return array
    */
    public function getArray($range,$rtarray=FALSE)
    {
        try {
            $result = $this->objPHPExcel->getActiveSheet()->rangeToArray($range);
            if ($rtarray){return $result;} else {return $result[0];}
        } catch(PHPExcel_Reader_Exception $e) {
            $this->systemLog->setLog($e->getMessage());
            return FALSE;
        }
    }
    
     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function getWorkSheetsInfo()
    {
        return $this->objReader->listWorksheetInfo($this->inputFileName);
    }

     
    
    /**
     * Convert BR tags to nl
     *
     * @param string The string to convert
     * @return string The converted string
     */
    function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }
    
    
    
    public function getMergeCells(){
        
        $objWorksheet = $this->objPHPExcel->getActiveSheet();

        if (count($objWorksheet->getMergeCells()) > 0){
            $char = '';
            foreach($objWorksheet->getMergeCells() as $c){
                $char .= $c.';';
            }

            return $char;
            
        } else {
            return false;
            
        }
        
        
    }

    
    public function __set($propiedad,$valor) {$this->$propiedad=$valor;}
    public function __get($propiedad) {return $this->$propiedad;}
    public function __isset($propiedad) {return isset($this->$propiedad);}
    public function __unset($propiedad) {unset ($this->$propiedad);}
}

class Application_Model_chunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow = 0;
    private $_endRow = 0;
    private $_columns  = array(); 
    
   /**  Get the list of rows and columns to read  */ 
        public function __construct($startRow, $endRow, $columns) { 
        $this->_startRow = $startRow; 
        $this->_endRow   = $endRow; 
        $this->_columns  = $columns; 
    } 
    
    public function readCell($column, $row, $worksheetName = '') { 
        //  Only read the rows and columns that were configured 
        if ($row >= $this->_startRow && $row <= $this->_endRow) { 
            if (in_array($column,$this->_columns)) { 
                return true; 
            } 
        } 
        return false; 
    } 
    

}

    