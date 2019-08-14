<?php
class Application_Model_DbTable_Generico_Grupos extends Zend_Db_Table {
	
	protected $_name = "t_grupos";
	protected $_primary = 'OU_ID';

	public function getNombre($ou_id){
    
	    $sql = $this->select()->from($this->_name, array('nombre'))->where('OU_ID=?', $ou_id);
        $result = $this->fetchRow($sql)->toArray();
	    
	    return count($result) > 0 ? $result : false;
    	    
	}
	
	
}

?>