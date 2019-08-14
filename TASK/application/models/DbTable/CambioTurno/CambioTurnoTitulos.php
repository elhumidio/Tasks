<?php
class Application_Model_DbTable_CambioTurno_CambioTurnoTitulos extends Zend_Db_Table {
	
	protected $_name = "t_cambio_turno_titulos";
	protected $_primary = 'id';

	public function get(){
	    $sql = $this->select()->order(array('titulo'));
	    $result = $this->fetchAll($sql);
	    return $result->toArray();
	}
	
	
	/**
	 * Inserta título
	 * @param string $titulo
	 */
	public function addTitulo($titulo)
	{
	   try {
	        $this->insert(array('titulo'=>$titulo));
	    } catch (Exception $e){
	        //file_put_contents('test.txt', print_r($e->getMessage(), true));
	    }  
	}
	
	/**
	 * Deletes titulo
	 * @param string $id
	 */
	public function deleteTitulo($id)
	{
	    try 
	    {
	        return parent::delete('id = '.$id);
	    }
	    catch (Exception $e)
	    {
	        return "KO";  
	    }
	}
	
	
	/**
	 * Updates titulo
	 * @param string $id
	 * @param string $titulo
	 */
	public function updateTitulo($id,$titulo)
	{
	    file_put_contents("idtitulo.txt",$id." - ".$titulo);
	    try
	    {
	        $values["titulo"] = $titulo;
	        return parent::update($values, 'id = '.$id);
	    }
	    catch (Exception $e)
	    {
	         return "KO";
	    }
	    
	}
	

		
}

?>