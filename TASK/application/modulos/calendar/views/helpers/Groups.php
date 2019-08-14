<?php
require_once 'Zend/View/Interface.php';

class Zend_View_Helper_Groups {

    public $view;

    public function Groups()
    {
        return $this;
    }


    public function GetAllGroups(){
         
        $groupsDb = new Application_Model_DbTable_Groups();

        $groups = $groupsDb->getGroups();

        $salida = array();

        foreach($groups as $group){
            $salida[] = '<option value="'.$group['group'].'">'.$group['group'].'</option>';
        }

        return $salida;

    }


}