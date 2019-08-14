<?php 
/**
 * Action Helper for finding days in a month
 */
 
class Zend_Controller_Action_Helper_Myparams extends Zend_Controller_Action_Helper_Abstract
{
    public $params;

    public function __construct()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->params = $request->getParams();
    }

    public function rmparams()
    {       
        if(isset($this->params['controller']))
            unset($this->params['controller']);
        if(isset($this->params['_access']))
            unset($this->params['_access']);
        if(isset($this->params['tabla']))
            unset($this->params['tabla']);
        if(isset($this->params['action']))
            unset($this->params['action']);
        if (isset($this->params['module']))
            unset($this->params['module']);
        if (isset($this->params['user']))
        	unset($this->params['user']);    
        if (isset($this->params['until']))
        	if($this->params['until'] =='')
        	unset($this->params['until']);
        if (isset($this->params['remark']))
            if($this->params['remark'] =='')
            unset($this->params['remark']);
        if (isset($this->params['idTarea']))
            if($this->params['idTarea'] =='')
            unset($this->params['idTarea']);
		/*if (!isset($this->params['minimo']))
			$this->params['minimo'] ='';*/
        return $this->params;
    }

	public function cleanexec()
	{
        $filter_params = array();
        $filer_recur = array();
        
        foreach($this->params as $key => $val) {
            if(substr($key, 0, 6) == '__@@__')
            {
                if(substr($key, 0, 9) == '__@@__re_'){
                    $filter_params['programacion'] =  $val;
                } else {
                    $filter_params[str_replace("__@@__","",$key)] = $val;}
            }
        }
        return $filter_params;
	}
	
	public function cleanprepdb($params)
	{
		if (isset($params['Name'])){
				$params['server'] = $params['Name'];
				unset($params['Name']);
			}
		if (isset($params['NBck'])){
				$params['VLANB'] = $params['NBck'];
				unset($params['NBck']);
		if (isset($params['vco_vxlan_pro_id']))
            unset($params['vco_vxlan_pro_id']);
		if (isset($params['vco_vxlan_pro_dunesUri']))
            unset($params['vco_vxlan_pro_dunesUri']);
		if (isset($params['vco_vxlan_cas_id']))
            unset($params['vco_vxlan_cas_id']);
		if (isset($params['vco_vxlan_cas_dunesUri']))
            unset($params['vco_vxlan_cas_dunesUri']);
		if (isset($params['vco_vxlan_bkp_id']))
            unset($params['vco_vxlan_bkp_id']);
		if (isset($params['vco_vxlan_bkp_dunesUri']))
            unset($params['vco_vxlan_bkp_dunesUri']);
			}			
		return $params;
	}
	
    public function direct()
    {
        return $this->rmparams();
    }
}