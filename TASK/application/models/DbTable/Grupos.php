<?php
class Application_Model_DbTable_Grupos extends Zend_Db_Table {

    protected $_name = "cal_grupos";
    protected $_primary = 'ID';

    public function getGrupos()
    {
        $select = parent::select();
        $result = parent::fetchAll($select)->toArray();
        return $result;
    }

    public function getGrupo($grupoid)
    {
        $select = parent::select()->from($this,array('nombre'))->where('ID =?',$grupoid);
        $result = parent::fetchRow($select)->toArray();
        return $result;
         
    }

    /**
     * Actualiza un cliente
     * @param string $id
     * @param string $data
     */
    public function updateGrupo($id, $data)
    {
        $values["activo"] = $data;
        return parent::update($values, 'ID = '.$id);
         
    }

    


}

?>