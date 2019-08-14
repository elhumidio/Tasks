<?php
/**
 * Clase que contiene los métodos utilizados por el plugin de javascript Data tables
 * 
 * Los métodos disponibles son:
 * 
 * process(): proceso SQL, devuelve array en json.
 * fatal_error(): de uso interno.
 * parseFecha(): parsea una fecha en formato mySQL (Y-m-d H:i:s) en el formato que le digamos.
 * parseIphoneStyle(): devuelve el código necesario para utilizar el plugin de jQuery IphoneStyle
 * 
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 2.2 - 02/01/2013
 *
 */

abstract class Application_Model_Datatables_Abstract
{	
	static protected $replaceColumns = array();
	
	/**
	 * Función privada que procesa el contenido de un datatables
	 * 
	 * @tutorial $sTable: DB table to use.
	 * @tutorial $sIndexColumn: Indexed column (used for fast and accurate table cardinality).
	 * @tutorial $aColumns: Array of database columns which should be read and sent back to DataTables. Use a space where you want to insert a non-database field (for example a counter or static image).
	 * @tutorial $trHaveId: You can specify the custom ID for rows. Ej, 'user_' returns 'user_X', where X equals the ID of the row.
	 * 
	 * @param string $sTable
	 * @param string $sIndexColumn
	 * @param array $aColumns
	 * @param string $where
	 * @param $trHaveId
	 * @return string
	 */
	static protected function process($sTable,$aColumns,$sIndexColumn='ID',$where=false,$trHaveId=false)
	{
		/* Database connection information */
		$options = Zend_Registry::get ( 'configuracion.ini' );
		
		$gaSql = array();
		
		$gaSql['server']					= $options['resources']['db']['params']['host'].':'.$options['resources']['db']['params']['port'];
		$gaSql['user']						= $options['resources']['db']['params']['username'];
		$gaSql['password']					= $options['resources']['db']['params']['password'];
		$gaSql['db']						= $options['resources']['db']['params']['dbname'];
		$gaSql['character_set_connection']	= $options['resources']['db']['params']['driver_options']['1002'];
		
		
		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
		* no need to edit below this line
		*/
		 
		 
		/*
		 * MySQL connection
		*/
		if ( ! $gaSql['link'] = @mysql_connect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) )
		{
			self::fatal_error( 'Could not open connection to server' );
		}
		 
		if ( ! mysql_select_db( $gaSql['db'], $gaSql['link'] ) )
		{
			self::fatal_error( 'Could not select database ' );
		}
		 $result = mysql_query("set names 'utf8'");
		 
		/*
		 * Paging
		*/
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
					intval( $_GET['iDisplayLength'] );
		}
		 
		
		/*
		 * Parche para poder utilizar nombres de funciones en las columnas
		 */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( substr_count($aColumns[$i], 'iphoneStyleRoles:') !== 0 )
			{
				$aColumns[$i] = str_replace('iphoneStyleRoles:', '', $aColumns[$i]);
				self::$replaceColumns[$aColumns[$i]] = 'iphoneStyleRoles';
			}
			
			if( substr_count($aColumns[$i], 'iphoneStyleModulos:') !== 0 )
			{
				$aColumns[$i] = str_replace('iphoneStyleModulos:', '', $aColumns[$i]);
				self::$replaceColumns[$aColumns[$i]] = 'iphoneStyleModulos';
			}
			
			if( substr_count($aColumns[$i], 'unserialize:') !== 0 )
			{
				$aColumns[$i] = str_replace('unserialize:', '', $aColumns[$i]);
				self::$replaceColumns[$aColumns[$i]] = 'unserialize';
			}
		}
		
		 
		/*
		 * Ordering
		*/
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "`".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."` ".
							($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
				}
			}
			 
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}
		}
		 
		 
		/*
		 * Filtering
		* NOTE this does not match the built-in DataTables filtering which does it
		* word by word on any field. It's possible to do here, but concerned about efficiency
		* on very large tables, and MySQL's regex functionality is very limited
		*/
		$sWhere = "";
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		 
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
			}
		}
		
		
		if ( $where )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE $where ";
			}
			else
			{
					
				$sWhere .= " AND $where ";
			}
		}
		 
		 
		/*
		 * SQL queries
		* Get data to display
		*/
		$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
		";
		$rResult = mysql_query( $sQuery, $gaSql['link'] ) or self::fatal_error( 'MySQL Error: ' . mysql_errno() );
				 
		/* Data set length after filtering */
		 $sQuery = "
		SELECT FOUND_ROWS()
		";
		$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or self::fatal_error( 'MySQL Error: ' . mysql_errno() );
				$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
				$iFilteredTotal = $aResultFilterTotal[0];
		 
		/* Total data set length */
		$sQuery = "
		SELECT COUNT(`".$sIndexColumn."`)
		FROM   $sTable
		";
		$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or self::fatal_error( 'MySQL Error: ' . mysql_errno() );
		$aResultTotal = mysql_fetch_array($rResultTotal);
		$iTotal = $aResultTotal[0];
				 
				 
		/*
		* Output
		*/
		$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
		);
		 
		while ( ($aRow = mysql_fetch_array( $rResult ))==true )
		{
			$row = array();
			
			if($trHaveId) $row['DT_RowId'] = $trHaveId.$aRow[ $sIndexColumn ];
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				/* FORMATO ESPECÍFICO PARA DETERMINADOS CAMPOS */
				switch($aColumns[$i])
				{
					case "created_at":
					case "last_access":
						$row[] = self::parseFecha($aRow[ $aColumns[$i] ]);
						Break;
					
					case isset(self::$replaceColumns[$aColumns[$i]]):
						
						switch(self::$replaceColumns[$aColumns[$i]])
						{
							case 'iphoneStyleRoles': $row[] = self::parseiphoneStyle('app_roles', $aRow['ID'], $aRow[ $aColumns[$i] ]); Break;
							case 'iphoneStyleModulos': $row[] = self::parseiphoneStyle('app_modulos', $aRow['ID'], $aRow[ $aColumns[$i] ]); Break;
							case 'unserialize': $row[] = self::unserializeArray($aRow[ $aColumns[$i] ]); Break;
						}
						
						Break;
						
					/* Este parche es necesario para eliminar los posibles resultados en blanco */
					case ' ': Break;
					/* General output */
					default: $row[] = $aRow[ $aColumns[$i] ]; Break;
				}
			}
			$output['aaData'][] = $row;
		}
		 
		return json_encode( $output );
	}
	
	
	
	/**
	 * Local functions for datatables
	 * @param string $sErrorMessage
	 */
	static protected function fatal_error ( $sErrorMessage = '' )
	{
		header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
		die( $sErrorMessage );
	}
	
	
	
	
	/**
	 * Dado un string de fecha devuelve el objeto parseado
	 * @param string $data (Y-m-d H:i:s)
	 * @param string $pattern
	 */
	static protected function parseFecha($data,$pattern='H:i \- jS F Y')
	{
		$object = new DateTime($data);
		return $object->format($pattern);
	}
	
	
	/**
	 * Inserta el código html necesario para mostrar el plugin de js iphoneStyle
	 */
	static protected function parseIphoneStyle($seccion,$id,$columna)
	{
		$salida = '<input name="'.$seccion.'#'.$id.'" type="checkbox" '.(($columna=="on" || $columna=="Y" || $columna=="si" || $columna=="1")?"checked":"").' /><span class="hidde">'.$columna.'</span>';
// 		$salida = '<input name="'.$seccion.'#'.$id.'" type="checkbox" '.(($columna=="on")?"checked":"").' />';
		return $salida;
	}
	
	
	/**
	 * Desserializa un array y le hace un implode.
	 */
	static protected function unserializeArray($columna)
	{
		return implode(', ',unserialize($columna));
	}
	
}