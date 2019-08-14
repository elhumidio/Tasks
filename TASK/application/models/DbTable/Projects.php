<?php
class Application_Model_DbTable_Projects extends Zend_Db_Table {

    protected $_name = "cal_id_proyectos_tratados";
    protected $_primary = 'id';

    /**
     * Obtiene la lista de proyectos tratados
     */
    public function getproyectos()
    {
        $select = parent::select();
        $result = parent::fetchAll($select)->toArray();
        return $result;
    }

    /**
     * Verifica si el proyecto ha sido tratado
     * @param string $idProyecto
     */
    public function checkProyecto($idProyecto,$idTarea)
    { //file_put_contents("resultCheck.txt" , $idProyecto.' - '.$idTarea,FILE_APPEND);
        //$where = array('idProyecto = ?',$idProyecto);
        $select = parent::select()
        ->where('idProyecto = ?',$idProyecto)
		->where('idTarea = ?',$idTarea);
        $result = parent::fetchAll($select);
       
        if($result->count())
        {
            return count($result) > 0 ? true : false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Inserta un proyecto que ha sido tratado
     * @param string $id
     */
    public function insertIdProj($id,$idTarea)
    {
        $arraydatos = array();
        $arraydatos["id"] = null;
        $arraydatos["idProyecto"] = $id;
		$arraydatos["idTarea"] = $idTarea;
		//file_put_contents("insertidproj.txt" , json_encode($arraydatos),FILE_APPEND);
		if(!$this->checkProyecto($id,$idTarea))
        return parent::insert($arraydatos);
    }
    


}

?>