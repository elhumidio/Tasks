<?php
class Application_Model_DbTable_CMDB_servidores extends Zend_Db_Table_Abstract
{
    protected $_name = "servidores";
    protected $_primary = 'ID';
    
    protected function _setupDatabaseAdapter() {
        $this->_db = Zend_Registry::get ( 'cmdb' );
        parent::_setupDatabaseAdapter ();
    }
    
   

    /**
	 * Gets server by name
	 * @param string $name
	 * @return array
	 */
	public function getServerByCIName($name)
	{
	    $this->_name = 'servidores';
	    $this->_primary = 'ID';
	    
	    $query = "SELECT distinct ip.*, s.* FROM  servidores s
	     LEFT JOIN replica_vista_ip ip on s.ID = ip.CIID
	     WHERE lower(s.HOSTNAME)  = '".strtolower($name)."'";
	    //  file_put_contents("queryservers.txt",$query);
	    $stmt = $this->_db->query($query);
	    $res = $stmt->fetchAll();
        
        if(count($res)>0)
	    {
	        return $res;
	    }
	    else return "NORESULTS";
	}
	
	
	/**
	 * Get servers by name
	 * @param string $name
	 */
	public function getServersByClusterName($name)
	{
	    $this->_name = 'replica_vista_cluster';
	    $this->_primary = 'ID';
	    
	    $query = "SELECT distinct rvc.NAME as CLUSTERNAME,rvc.ESTADO as ESTADOCLUSTER,rvc.PROPIETARIO as CLUSTERPROPIETARIO, 
	              rvc.DESCRIPCION as CLUSTERDESCRIPCION,
                  rvc.OBSERVACIONES as CLUSTEROBSERVACIONES, rvc.CLUSTERALIAS, rvc.ALIASBACKUP as CLALIASBACKUP, rvc.CLUSTERTIPO, rvc.CLUSTERFUNCIONES,
                  s.*  
                  FROM replica_vista_cluster rvc 
                  INNER JOIN servidores s on rvc.SRVVID = s.ID
                  WHERE lower(rvc.NAME)  = '".strtolower($name)."'";
	   // file_put_contents("queryclusters.txt",$query);
	    $stmt = $this->_db->query($query); 
	    
	    $rows = $stmt->fetchAll();
        	   
	    for($i = 0; $i < count($rows); $i++)
	    {
	        $rows[$i]['ips'] = $this->getIpsFromServer($rows[$i]['ID']);
	        
	    }
	    if(count($rows) > 0)
        	return $rows;
    	else 
    		return "NORESULTS";
	    
	}
	
	
	public function getIpsFromServer($ID)
	{
	   
	    $this->_name = 'replica_vista_cluster';
	    $this->_primary = 'IPID';
	    $query = "SELECT * from replica_vista_ip WHERE CIID = ".$ID;
	    $stmt = $this->_db->query($query);
	    $rows = $stmt->fetchAll();
	    return $rows;
	}
	
    
    
}