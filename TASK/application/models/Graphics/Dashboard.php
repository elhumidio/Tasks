<?php
/**
 * Esta clase contienen los objetos que generan los gráficos de estadísticas
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 3.0 - 180/01/2013
 *
 */

class Application_Model_Graphics_Dashboard extends Application_Model_Graphics_Abstract
{
	/**
	 * Devuelve un array en formato json con los datos necesarios para llenar una gráfica Gauge
	 * @param string $title
	 * @param int $uid
	 * @param array $datos
	 */
	static public function Gauge($title,$uid,$datos)
	{
		$data = array();
		$data['cols'][] = array("label"=>'Label', "pattern"=>"", "type"=>"string");
		$data['cols'][] = array("label"=>'Value', "pattern"=>"", "type"=>"number");
		$data['rows'] = array();
	
	
		foreach($datos as $row)
		{
			$data['rows'][] = array('c'=>array(array('v'=>$row['label']),array('v'=>(int)$row['valor'])));
		}
	
		$var = Zend_Json::encode($data);
	
		return $title.'#'.$var;
	}
	
	
	/**
	 * Devuelve un array en formato json con los datos necesarios para llenar una gráfica LineChart / ComboChart
	 * @param string $kpiName
	 * @param string $titulo
	 * @param int $month
	 * @param int $year
	 */
	static public function HistoricoDiario($IDR,$kpiName,$titulo,$month,$year,$client_data,$grupo=false)
	{
		$cliente = $client_data['moduleName'];
		
		$translator = Zend_Registry::get('Zend_Translate');
		$adapter = $translator->getAdapter();
		
		$data = array();
		$data['cols'][] = array('id'=>'', 'label'=>$adapter->translate('Día'), 'type'=>'string');
		$data['cols'][] = array('id'=>'', 'label'=>$adapter->translate('SLA OK'), 'type'=>'number');
		$data['cols'][] = array('id'=>'', 'label'=>$adapter->translate('SLA KO'), 'type'=>'number');
		//$data['cols'][] = array('id'=>'', 'label'=>'Casos', 'type'=>'number');
		$data['rows'] = array();

		
		$dbkpi = new Application_Model_DbTable_TmpVistaDescripcion();
		$kpiData = $dbkpi->getByKpiName($kpiName);

		if($kpiData['TIPO']!='Manual'){
	// 		$kpiData = Application_Model_Dashboard_Kpi::checkKpiDetails($kpiData,$year,$month);
			
			$primerDia = "$year-$month-01";
			$ultimoDia = Application_Model_Dashboard_Abstract::calcularUltimoDiaMes($primerDia,true);
			
			$cache = Zend_Registry::get ( 'Cache' );
			
			if( ($result = $cache->load(md5($cliente.'_'.$kpiData['ID'].'_Historico_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)))) === false )
			{
				$consulta = array('FECHA','CASOS'=>'SUM(CASOS)','SLAOKSUM'=>'SUM(SLAOK)','SLAKOSUM'=>'SUM(SLAKO)');
				$db = new Application_Model_DbTable_TmpVistaDiaria();
	// 			$sql = $db->select()->from($db,$consulta)->where('CLIENTE = ?',ucfirst(strtolower($cliente)))->where("KPI = ?", $kpiName)->where('FECHA >= ?',$primerDia)->where('FECHA <= ?',$ultimoDia)->group('FECHA');
				$sql = $db->select()->from($db,$consulta)->where('IDR = ?',$IDR)->where('UCASE(CLIENTE) = ?',strtoupper($client_data['name']))->where("KPI = ?", $kpiName)->where('FECHA >= ?',$primerDia)->where('FECHA <= ?',$ultimoDia)->group('FECHA');
				
				if(isset($grupo) && !empty($grupo)) $sql->where('GRUPO = ?',$grupo);
				$result = $db->fetchAll($sql)->toArray();
				$cache->save($result, md5($cliente.'_'.$kpiData['ID'].'_Historico_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)));
			}
					
			foreach($result as $row)
			{
				$t = new DateTime($row['FECHA']);
				$data['rows'][] = array('c'=>array(array('v'=>$t->format("d"),'f'=>$t->format("d")),array('v'=>(int)$row['SLAOKSUM']),array('v'=>(int)$row['SLAKOSUM'])));
			}
			
			return ($grupo?"$grupo - ":'').$kpiName.': '.$titulo.'#'.Zend_Json::encode($data);
		}
	}
	
	
	/**
	 * Devuelve un array en formato json con los datos necesarios para llenar una gráfica LineChart / ComboChart
	 * @param string $kpiName
	 * @param string $titulo
	 * @param int $month
	 * @param int $year
	 */
	static public function HistoricoMensual($IDR,$kpiName,$titulo,$month,$year,$client_data,$grupo=false)
	{
		$cliente = $client_data['moduleName'];
		
		$translator = Zend_Registry::get('Zend_Translate');
		$adapter = $translator->getAdapter();
		
		$data = array();
		$data['cols'][] = array("label"=>$adapter->translate('Mes'), "pattern"=>"", "type"=>"string");
		$data['cols'][] = array("label"=>$adapter->translate('Volumetría'), "pattern"=>"", "type"=>"number");
		$data['rows'] = array();
	
	
		$dbkpi = new Application_Model_DbTable_TmpVistaDescripcion();
		$kpiData = $dbkpi->getByKpiName($kpiName);
	
		
		$primerDia = "$year-$month-01";
		$ultimoDia = Application_Model_Dashboard_Abstract::calcularUltimoDiaMes($primerDia,true);
			
		$cache = Zend_Registry::get ( 'Cache' );
			
		if( ($result = $cache->load(md5($cliente.'_'.$kpiData['ID'].'_Historico_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)))) === false )
		{
			$consulta = array('FECHA','cumplimiento'=>$kpiData['FORMULA'],'volumetria'=>'SUM(CASOS)');
			$db = new Application_Model_DbTable_TmpVistaMensual();
			$sql = $db->select()->from($db,$consulta)->where('IDR = ?',$IDR)->where('UCASE(CLIENTE) = ?',strtoupper($client_data['name']))->where("KPI = ?", $kpiName)->where('FECHA >= ?',$primerDia)->where('FECHA <= ?',$ultimoDia)->group('FECHA');

// 			$sql = $db->select()->from($db,$consulta)->where('UCASE(CLIENTE) = ?',strtoupper($client_data['name']))->where("KPI = ?", $kpiName)->where('MONTH(FECHA) <= ?',$month)->where('YEAR(FECHA) <= ?',$year)->group('MONTH(FECHA)')->limit(13)->order('YEAR(FECHA) ASC')->order('MONTH(FECHA) ASC');
				
			
			if(isset($grupo) && !empty($grupo)) $sql->where('GRUPO = ?',$grupo);
			$result = $db->fetchAll($sql)->toArray();
			$cache->save($result, md5($cliente.'_'.$kpiData['ID'].'_Historico_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)));
		}
			
		$i = 0;
		foreach($result as $row)
		{
			$t = new DateTime($row['FECHA']);
			$volumetria = isset($row['volumetria'])?(int)$row['volumetria']:NULL;
			$objetivo= isset($objetivo)?(int)$objetivo:NULL;
			$cumplimiento = isset($row['cumplimiento'])?(int)round($row['cumplimiento']*100,Application_Model_Dashboard_Abstract::ROUND_PRECISION,PHP_ROUND_HALF_UP):NULL;
			
			$data['rows'][] = array('c'=>array(array('v'=>$t->format("M Y")),array('v'=>$volumetria),array('v'=>$objetivo,'f'=>"$objetivo%"),array('v'=>$cumplimiento,'f'=>"$cumplimiento%")));
			$i++;
			if($i==13) break;
		}
	
		return ($grupo?"$grupo - ":'').$kpiName.': '.$titulo.'#'.Zend_Json::encode($data);
		
	}
	
	
	/**
	 * Devuelve un array en formato json con los datos necesarios para llenar una gráfica LineChart / ComboChart
	 * @param string $kpiName
	 * @param string $titulo
	 * @param int $month
	 * @param int $year
	 */
	static public function Evolucion($IDR,$kpiName,$objetivo,$titulo,$month,$year,$client_data,$periodo,$grupo=false)
	{
		$cliente = $client_data['moduleName'];
		
		$translator = Zend_Registry::get('Zend_Translate');
		$adapter = $translator->getAdapter();
		
		$data = array();
		$data['cols'][] = array("label"=>$adapter->translate('Mes'), "pattern"=>"", "type"=>"string");
		$data['cols'][] = array("label"=>$adapter->translate('Volumetría'), "pattern"=>"", "type"=>"number");
		$data['cols'][] = array("label"=>$adapter->translate('Objetivo'), "pattern"=>"", "type"=>"number");
		$data['cols'][] = array("label"=>$adapter->translate('Cumplimiento'), "pattern"=>"", "type"=>"number");
		
		$data['rows'] = array();
	
		$dbkpi = new Application_Model_DbTable_TmpVistaDescripcion();
		$kpiData = $dbkpi->getByKpiName($kpiName);
	
// 		$kpiData = Application_Model_Dashboard_Kpi::checkKpiDetails($kpiData,$year,$month);
	
		$cache = Zend_Registry::get ( 'Cache' );
		if( ($result = $cache->load(md5($cliente.'_'.$kpiData['ID'].'_Evolucion_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)))) === false )
		{			
			$consulta = array('FECHA','cumplimiento'=>$kpiData['FORMULA'],'volumetria'=>'SUM(CASOS)');
			$db = new Application_Model_DbTable_TmpVistaMensual();
// 			$sql = $db->select()->from($db,$consulta)->where('UCASE(CLIENTE) = ?',strtoupper($client_data['name']))->where("KPI = ?", $kpiName)->where('DATE(FECHA) <= ?',"$year-$month-01")->group('MONTH(FECHA)')->limit(13)->order('YEAR(FECHA) ASC')->order('MONTH(FECHA) ASC');
			$sql = $db->select()->from($db,$consulta)->where('IDR = ?',$IDR)->where('UCASE(CLIENTE) = ?',strtoupper($client_data['name']))->where("KPI = ?", $kpiName)->where('DATE(FECHA) <= ?',"$year-$month-01")->group('MONTH(FECHA)')->limit(13)->order('YEAR(FECHA) ASC')->order('MONTH(FECHA) ASC');
			if(isset($grupo) && !empty($grupo)) $sql->where("GRUPO = ?",$grupo);
			$result = $db->fetchAll($sql)->toArray();
			
// 			echo $sql->__toString();
// 			echo '<br />';
			
			$cache->save($result,md5($cliente.'_'.$kpiData['ID'].'_Evolucion_'.$month.'_'.$year.'_'.(is_null($grupo)?'total':$grupo)));
		}
		
		// Este array es necesario para parchear los meses faltantes en la BD.
// 		$meses = array(
// 				array('FECHA'=>'2011-10-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2011-11-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2011-12-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-01-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-02-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-03-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-04-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-05-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-06-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-07-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-08-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-09-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-10-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-11-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL),
// 				array('FECHA'=>'2012-12-01 00:00:00','cumplimiento' =>NULL,'volumetria'=>NULL,'objetivo'=>NULL)
// 			);
		
// 		$result = array_merge($meses,$result);
		$result = array_reverse($result);
		
		$i = 0;
		foreach($result as $row)
		{
			$t = new DateTime($row['FECHA']);
			$volumetria = isset($row['volumetria'])?(int)$row['volumetria']:NULL;
			$objetivo= isset($objetivo)?(int)$objetivo:NULL;
			$cumplimiento = isset($row['cumplimiento'])?(int)round($row['cumplimiento']*100,Application_Model_Dashboard_Abstract::ROUND_PRECISION,PHP_ROUND_HALF_UP):NULL;
			
			$data['rows'][] = array('c'=>array(array('v'=>$t->format("M Y")),array('v'=>$volumetria),array('v'=>$objetivo,'f'=>"$objetivo%"),array('v'=>$cumplimiento,'f'=>"$cumplimiento%")));
			$i++;
			if($i==13) break;
		}
	
		return ($grupo?"$grupo - ":'').$kpiName.': '.$titulo.'#'.Zend_Json::encode($data);
	}
		
}