<?php
class Application_Model_DbTable_Calendar_UsuariosEventos extends Zend_Db_Table {
	
	protected $_name = "cal_eventos_username";
	protected $_primary =array('idEvento','username','OU_ID');
	
    
    public function InsertData($data) {
        return parent::insert($data);
    }	
    
     public function AsignarUsuario($idEvento,$username)
    {
        $data = array('username' => $username);
        $where['idEvento =?'] = $idEvento ;
        try{
         if(is_int(parent::update($data, $where)))return true;    
          
        }catch(Zend_Exception $e){
            return array('fail'=>array($e->getCode()=>$e->getMessage()));
        }

    }
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>