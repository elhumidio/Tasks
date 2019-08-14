<?php
class Application_Model_Calendar_ModelsClient
{
    public function __construct(){}
    
    
    /**
     * Gets Servers info
     * @param string $ci
     * @return array
     */
    public function GetServersByCI($ci)
    {
        try{
            $model = new Application_Model_DbTable_CMDB_servidores();
             
            $servers = $model->getServerByCIName($ci);
            
            return $servers;
        }catch(Exception $ex){
            file_put_contents("GetServersByCI_Exception",$ex);
        }
        
        
    }
    
    /**
     * Gets servers clusters
     * @param string $ci
     * @return array
     */
    public function getServersByClusterName($ci)
    {
        try{
            $model = new Application_Model_DbTable_CMDB_servidores();
             
            $servers = $model->getServersByClusterName($ci);
        
            return $servers;
        }catch(Exception $ex){
            file_put_contents("exception_getServersByClusterName.txt",$ex);
        }
        
    }
    
}