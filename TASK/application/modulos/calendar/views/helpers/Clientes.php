<?php

require_once 'Zend/View/Interface.php';

class Zend_View_Helper_Clientes {
    
    public $view;
    
	public function Clientes()
	{
		return $this;
	}
	 
    
    public function GetAll(){
     
        $cliDb = new Application_Model_DbTable_Clientes();
        
        $clientes = $cliDb->getClientes();
        
        $salida = array();
        
        foreach($clientes as $cliente){
            $salida[] = '<option tipo="'.$cliente['Tipo'].'" value="'.$cliente['ID'].'">'.$cliente['nombre'].'</option>';
        }
        
        return $salida;
        
    }
    
    
}