<?php

class AdminController extends Zend_Controller_Action
{

	protected $user;
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
    public function init()
    {
    	$this->_helper->layout->setLayout('administracion');
    	if ($this->view->User()->hasRole('SDM') || $this->view->User()->hasRole('Admin')) $this->view->debug = 1;
    	else $this->view->debug = 0;
    	
    	$this->view->headLink()->appendStylesheet ( '/estilos/DataTables-1.9.1/media/css/jquery.dataTables.css','screen,print');
    	$this->view->headLink()->appendStylesheet ( '/estilos/DataTables-1.9.1/extras/TableTools-2.1.1/media/css/TableTools.css','screen,print');
    	 
    	$this->view->headScript()->appendFile ( '/estilos/js/iphone-style-checkboxes.js' );
    	$this->view->headScript()->appendFile ( '/estilos/DataTables-1.9.1/media/js/jquery.dataTables.js' );
    	$this->view->headScript()->appendFile ( '/estilos/DataTables-1.9.1/extras/TableTools-2.1.1/media/js/TableTools.min.js' );
    }

    
    /**
     * 
     */
    public function indexAction()
    {
        // action body
    	
//     	$db = Zend_Registry::get('pentaho');
//     	$sql = $db->select()->from('data_portal_wiw_org')->where('OU_Name like (?)','%T-Systems%');
//     	$result = $db->fetchAll($sql);
    	
//     	$db2 = new Application_Model_DbTable_AppDelegations();
//     	$db2->importFromWiW($result);

    	

    }
    
    
    
	public function refresrecursosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$front = $this->getFrontController();
    	$this->view->recursos = Application_Model_Recursos::search($front);
		
    }
    
    /**
     *
     */
    public function generalAction()
    {
    	
    	
    }
    
    
    /**
     * AJAX HOME
     */
    public function ajaxhomeAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('config/home');
    	 
    	 
    	$form = new Zend_Form();
    	$form->setAction(Zend_Registry::get('urlBase').'/admin/ajax_home')->setMethod('post');
    	$form->setAttrib('id', 'variables_de_sistema');
    	$form->setAttrib('class', 'config');
    
    	$db = new Application_Model_DbTable_AppConfig();
    
    	$result = $db->fetchAll($db->select());
    
    	foreach($result->toArray() as $value)
    	{
    		$form->addElement(
    				$value['type'],
    				$value['name'],
    				array(
    						'value'=>$value['value'],
    						'label' => $value['label'],
    						'validators' => unserialize($value['validators']),
    						'required' => $value['required'],
    						'filters'  => unserialize($value['filters']),
    						'multiOptions' => unserialize($value['multiOptions'])
    				)
    		);
    	}
    
    
    	$form->addElement(
    			'submit',
    			'Guardar',
    			array('ignore' => true)
    	);
    
    	$this->view->formulario = $form;
    
    
    	if ($this->getRequest()->isPost() && $this->_getParam('app_status'))
    	{
    		if ($form->isValid($_POST))
    		{
    			$post = $form->getValidValues($_POST);
    
    			foreach($post as $key => $val)
    			{
    				$where = $db->getAdapter()->quoteInto('name = ?', "$key");
    				$db->update(array('value'=>$val),$where);
    				
    			}
    			
    			$this->_redirect ('/admin/general');
    		}
    	}
    
    }
    
    /**
     * AJAX MODULOS
     */
    public function ajaxmodulosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('config/modules');
    
    	$form = new Application_Form_NewModule();
    	
    	$db = new Application_Model_DbTable_AppModulos();
    	$dbrol = new Application_Model_DbTable_AppRoles();
    	$dbrecursos = new Application_Model_DbTable_AppRecursos();
    	
    	if ($this->getRequest()->isPost() && $this->_getParam('appName'))
    	{
    		if ($form->isValid($_POST))
    		{
    			$post = $form->getValidValues($_POST);
    			
    			// Creo el ROL
    			$rol = $dbrol->checkExiste($post['rol']);
    			if($rol) $rol_id = $dbrol->insert(array('name'=>$post['rol'],'orden'=>'-1','estado'=>'off'));
    			else $this->_redirect ('/admin/general/error/2');
    			
    			// Creo el Recurso
    			$recurso = $dbrecursos->checkExiste('default','mods',$post['recurso']);
    			if($recurso) $recurso_id = $dbrecursos->insert(array('module'=>'default','controller'=>'mods','action'=>$post['recurso']));
    			else $this->_redirect ('/admin/general/error/1');

    			$dbpermisos = new Application_Model_DbTable_AppPermisos();
    			
    			// Asigno los permisos de roles al recurso nuevo
    			foreach($dbrol->getRoles() as $row)
    			{
    				$dbpermisos->insert(array('app_recursos_id'=>$recurso_id,'app_roles_id'=>$row['ID'],'access'=>($rol_id==$row['ID'] || $row['name']=='Admin' )?'allow':'deny'));
    			}
    			
    			// Borro las variables que no necesito
    			unset($post['rol']);
    			
    			$post['parametros'] = $post['parametros']==''?NULL:$post['parametros'];
    			$post['sistema_easya'] = $post['dominio']=='%EASYA%'?'dentro':'fuera';
    			$post['beta'] = 'N';
    			
    			// Guardo el módulo
    			$db->insert($post);
    			
    			$this->_redirect ('/admin/general');
    		}
    	}
    	
    	if ($this->_getParam('borrar'))
    	{
    		$idModulo = $this->_getParam('borrar');
    		
    		$modulo = $db->fetchRow($db->select()->where('ID = ?', $idModulo));
    		$moduloArray = $modulo->toArray();
    		
    		$recurso = $dbrecursos->fetchRow($dbrecursos->select()->where('controller = ?', 'mods')->where('action = ?', $moduloArray['recurso']));
			
    		// Borro el módulo
    		$modulo->delete();
    		// Borro el recurso
    		$recurso->delete();
    	}
    	
    	$this->view->form = $form;
    }
    
    
    
    
    
    /**
     *
     */
    public function countryAction()
    {
    	$form = new Zend_Form();
    	$form->setAction('/admin/general')->setMethod('post');
    	$form->setAttrib('id', 'variables_de_sistema');
    	 
    	$db = new Application_Model_DbTable_AppConfig();
    	 
    	$result = $db->fetchAll($db->select());
    	 
    	foreach($result->toArray() as $value)
    	{
    		$form->addElement(
    				$value['type'],
    				$value['name'],
    				array(
    						'value'=>$value['value'],
    						'label' => $value['label'],
    						'validators' => unserialize($value['validators']),
    						'required' => $value['required'],
    						'filters'  => unserialize($value['filters']),
    						'multiOptions' => unserialize($value['multiOptions'])
    				)
    		);
    	}
    	 
    	 
    	$form->addElement(
    			'submit',
    			'Guardar',
    			array('ignore' => true)
    	);
    	 
    	$this->view->formulario = $form;
    	 
    	 
    	if ($this->getRequest()->isPost())
    	{
    		if ($form->isValid($_POST))
    		{
    			$post = $form->getValidValues($_POST);
    			 
    			foreach($post as $key => $val)
    			{
    				$where = $db->getAdapter()->quoteInto('name = ?', "$key");
    				$db->update(array('value'=>$val),$where);
    			}
    		}
    	}
    	 
    	//echo serialize(array('StringToLower'));
       
    }
    
    
    /**
     *
     */
    public function delegationsAction()
    {
    	
    	if ($this->getRequest()->isPost())
    	{
    		$dbpo = new Application_Model_DbTable_AppDelegationsRelAplicaciones();
    		
    		$post = $this->getRequest()->getParams();

    		unset($post['controller'],$post['action'],$post['module'],$post['Guardar']);
    		
    		if(!isset($post['addapp'])) // al no recibir este parámetro significa que estamos guardando valores de alguna configuración
    		{
	    		$ID = $post['ID'];
	    		
	    		unset($post['id_delegations'],$post['id_aplicaciones'],$post['ID']);
	    		
	    		if(isset($post['password']))$post['password'] = base64_encode($post['password']);
	    		
	    		$post = serialize($post);
	    		
	    		
	    		$dbpo->update(array('value'=>$post),"ID=$ID");
    		}
    		else // en cambio si lo recibimos significa que estámos relacionando aplicación con delegación
    		{
    			unset($post['addapp']);
    			
    			if(isset($post['id_aplicaciones']))
    			{
	    			foreach($post['id_aplicaciones'] as $app)
	    			{
	    				if(!isset($post['ignore']))
	    				{
	    					$dbpo->insert(array('id_delegations'=>$post['id_delegations'],'id_aplicaciones'=>$app));
	    				} 
	    				else if(isset($post['ignore']) && !in_array($app,$post['ignore']))
	    				{
	    					$dbpo->insert(array('id_delegations'=>$post['id_delegations'],'id_aplicaciones'=>$app));
	    				}
	    			}
    			}
    			
    			if(isset($post['ignore']))
    			{
    				if(isset($post['id_aplicaciones']))
    				{
    					$diff = array_diff($post['ignore'],$post['id_aplicaciones']);
    				}
    				else
    				{
    					$diff = $post['ignore'];
    				}
    				
    				foreach($diff as $dif)
    				{
    					$dbpo->delete('id_delegations='.$post['id_delegations'].' AND id_aplicaciones='.$dif);
    				}
    			}
    		}
    	}
    }
    
    
    /**
     *
     */
    public function delegationsmodAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	if ($this->getRequest()->isPost())
    	{
    		$dbpo = new Application_Model_DbTable_AppDelegationsRelModulos();
    
    		$post = $this->getRequest()->getParams();
    
    		unset($post['controller'],$post['action'],$post['module'],$post['Guardar'],$post['addapp']);
    
    		
    			if(isset($post['id_modulos']))
    			{
    				foreach($post['id_modulos'] as $app)
    				{
    					if(!isset($post['ignore']))
    					{
    						$dbpo->insert(array('id_delegations'=>$post['id_delegations'],'id_modulos'=>$app));
    					}
    					else if(isset($post['ignore']) && !in_array($app,$post['ignore']))
    					{
    						$dbpo->insert(array('id_delegations'=>$post['id_delegations'],'id_modulos'=>$app));
    					}
    				}
    			}
    			 
    			if(isset($post['ignore']))
    			{
    				if(isset($post['id_modulos']))
    				{
    					$diff = array_diff($post['ignore'],$post['id_modulos']);
    				}
    				else
    				{
    					$diff = $post['ignore'];
    				}
    
    				foreach($diff as $dif)
    				{
    					$dbpo->delete('id_delegations='.$post['id_delegations'].' AND id_modulos='.$dif);
    				}
    			}
    		
    	}
    	
    	$this->_redirect ('/admin/delegations');
    }
    
    
    
    /**
     * AJAX DELEGACIONES
     */
    public function ajaxdelegacionesAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/delegaciones');
    	
    }
    
    /**
     * AJAX DELEGACIONES OPCIONES DE CONFIGURACIÓN
     */
    public function ajaxaplicacionesAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/aplicaciones');

    }
    
    
    /**
     * 
     */
    public function permisosyrolesAction()
    {
    	
    }
    
    
    /**
     * 
     */
    public function ajaxpermisosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/permisos');
    	
    	$dbr = new Application_Model_DbTable_AppRoles();
    	$this->view->roles = $dbr->getRolesOn();

//     	echo '<pre>';
//     	print_r($this->view->roles);
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$select = $db->select()->from(array('a'=> 'app_recursos'),array('IDR'=>'ID','controller','action'))
    						->joinLeft(array('b'=>'app_permisos'), 'b.app_recursos_id=a.ID',array('ID','access'))
    						->joinLeft(array('c'=>'app_roles'), 'c.ID=b.app_roles_id',array('name','hereda'))
    						
    						->order('c.ID DESC');
    	
    	$results = $db->fetchAll($select);
    	
//     	echo $select->__toString();
    	
    	$array = array();
    	$i = 0;
    	
    	foreach($results as $result)
    	{
    		if ($this->view->acl->isAllowed($this->view->user->username, $result['controller'], $result['action']))
    		{
    			$array[$result['controller'].'_'.$result['action']]['IDR'] = $result['IDR'];
    			$array[$result['controller'].'_'.$result['action']]['controller'] = $result['controller'];
    			$array[$result['controller'].'_'.$result['action']]['action'] = $result['action'];
    			$array[$result['controller'].'_'.$result['action']][$result['name']]['ID'] = $result['ID'];
    			
    			// Checheo si del que hereda tiene valor, si es así lo dejo en blanco.
    			if(isset($array[$result['controller'].'_'.$result['action']][$result['hereda']]['access']) && $array[$result['controller'].'_'.$result['action']][$result['hereda']]['access']=='allow')
    			{
    				$array[$result['controller'].'_'.$result['action']][$result['name']]['access'] = $array[$result['controller'].'_'.$result['action']][$result['hereda']]['access'];
    				$array[$result['controller'].'_'.$result['action']][$result['name']]['hereda'] = true;
    			}
    			else
    			{
    				$array[$result['controller'].'_'.$result['action']][$result['name']]['access'] = $result['access'];
    			}
    			
	    		$array[$result['controller'].'_'.$result['action']][$result['name']]['padre'] = $result['hereda'];
	    		$i++;
    		}
    	}

    	$this->view->list = $array;
    	
    	
    	//echo '<pre class="clear">';
    	//print_r($array);
    	
    	//die();
    	/**/
    	
    	
    }
    
    
    /**
     * 
     */
    public function ajaxrecursosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/recursos');
    	
    	//$db = new Application_Model_DbTable_AppRecursos();
    	//$this->view->list = $db->fetchAll($db->select());
    }
    
    
    /**
     * 
     */
    public function ajaxrolesAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/roles');
    	
    	//$db = new Application_Model_DbTable_AppRoles();
    	//$this->view->list = $db->fetchAll($db->select());
    }
    
    public function rolesAction()
    {
    
    }
    
    public function permisosAction()
    {
    	$dbRoles = new Application_Model_DbTable_AppRoles();
    	$this->view->rolesList = $dbRoles->getRoles();
    
    	$rol_id = $this->_getParam('rol_id',0);
    	$this->view->rol_id = $rol_id;
    
    	if($this->_getParam('check')):
    	// Guardo el estado del rol
    	Application_Model_Roles::GuardarEstado($rol_id,$this->_getParam('status'));
    	// Guardo los privilegios
    	Application_Model_Roles::GuardarPrivilegios($rol_id,$this->_getParam('check'),$this->_getParam('copiarol'));
    	// Copio los privilegios de otro rol.
    	Application_Model_Roles::CopiarPrivilegios($rol_id,$this->_getParam('copiarol'));
    	endif;
    }
    
    public function getpermisosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/permisos');
    
    	$id = $this->_getParam('id');
    	$this->view->id = $id;
    
    	//     	if($id=='1') return;
    
    	$dbRoles = new Application_Model_DbTable_AppRoles();
    
    	$this->view->activeRoleStatus = $dbRoles->getName($id,'estado');
    	$this->view->rolesList = $dbRoles->getRoles();
    
    	$dbRecursos = new Application_Model_DbTable_AppRecursos();
    	$dbPermisos = new Application_Model_DbTable_AppPermisos();
    
    	$select = $dbRecursos->select()
    	->setIntegrityCheck(false)
    	->from(array('a'=>$dbRecursos->__get('_name')))
    	->joinLeft(array('b'=>$dbPermisos->__get('_name')),'a.ID=b.app_recursos_id',array('access'))
    	->where('b.app_roles_id = ?', $id)
    	->order('a.controller ASC');
    
    	$this->view->recursosList = $dbRecursos->fetchAll($select);
    
    
    }
    
    public function generaindicesparaelrolAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	echo Application_Model_Roles::GenerarIndices($this->_getAllParams());
    }
    
    //     public function guardarpermisosderolAction()
    //     {
    //     	$this->_helper->layout->disableLayout();
    //     	$this->_helper->viewRenderer->setNoRender();
     
    //     	echo Application_Model_Roles::GuardarPrivilegios($this->_getAllParams());
    //     }
    
    public function recursosAction()
    {
    
    }
    
    /**
     * 
     */
    public function gruposAction()
    {
    	$db = new Application_Model_DbTable_Grupos();
    	$this->view->list = $db->fetchAll($db->select());
    }
    
    /**
     *
     */
    public function ajaxnewgruposAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setRender('ajax/new-grupos');
    	 
    	$this->view->formulario = new Application_Form_NewGrupo();
    }
    
    
    
    /**
     * 
     */
    public function usuariosAction()
    {
    	$db = new Application_Model_DbTable_User();
    	//$this->view->list = $db->fetchAll($db->select());
    }
    
    
    /**
     *
     */
    public function usuarioslistAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }
    
    
    
    public function gestpermisosAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$db = new Application_Model_DbTable_AppPermisos();
    	
    	$id = $this->_getParam('id');
    	$access = $this->_getParam('access');
    	$app_recursos_id = $this->_getParam('app_recursos_id');
    	$app_roles_id = $this->_getParam('app_roles_id');
    	
//     	list($id,$rol_id) = explode('~',$id);
    	
    	
    	switch($access)
    	{
    		case 'allow':
    			
    			if(isset($id)) $db->update(array('access'=>'deny'),"ID=$id");
    			else if(isset($app_recursos_id) && isset($app_roles_id)) $db->insert(array('app_recursos_id'=>$app_recursos_id,'app_roles_id'=>$app_roles_id,'access'=>'deny'));
    			
    			echo '/img/deny.png';
    			Break;
    		
    		case 'deny':
    			
    			if(isset($id)) $db->update(array('access'=>'allow'),"ID=$id");
    			else if(isset($app_recursos_id) && isset($app_roles_id)) $db->insert(array('app_recursos_id'=>$app_recursos_id,'app_roles_id'=>$app_roles_id,'access'=>'allow'));
    			
    			echo '/img/allow.png';
    			Break;
    	}
    }
    
    
    
    
    /**
     * Devuelve la lista de roles en formato json
     */
    public function geterollistAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$db = new Application_Model_DbTable_AppRoles();
    	$result = $db->getRolesName();
    	$roles = unserialize($this->view->user->role_id);
    	sort($roles);
    	$array = array();
    	foreach($result as $b)
    	{
    		if($b['ID'] > $roles[0])
    			$array[$b['name']] = $b['name'];
    	}
    	
    	echo json_encode($array);
    }
    
    /**
     * Modifica el rol del usuario
     */
    public function setrolAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$id = $this->_getParam('id');
    	$value = $this->_getParam('value');
    	
    	Application_Model_User::setRol($id,$value);
    	
    	echo implode(', ',array_reverse($value));
    }
    
    /**
     * Devuelve la lista de paises en formato json
     */
    public function getepaislistAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$db = new Application_Model_DbTable_Organigrama();
    	$result = $db->getCountryCode();
    	 
    	$array = array();
    	foreach($result as $b)
    	{
    		if(!in_array($b['Manager_CountryCode'],$array) && !empty($b['Manager_CountryCode']))
    			$array[$b['Manager_CountryCode']] = $b['Manager_CountryCode'];
    	}
    	 
    	echo json_encode($array);
    }
    
    
    /**
     * Modifica el pais del usuario
     */
    public function setpaisAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$id = $this->_getParam('id');
    	$value = $this->_getParam('value');
    	
    	Application_Model_User::setCountry($id,$value);
    	 
    	echo $value;
    }
    
    
    
    /**
     * Modifica el nombre de usuario de un usuario
     */
    public function updateusernameAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$id = $this->_getParam('id');
    	$value = $this->_getParam('value');
    	 
    	$salida = Application_Model_User::updateUsername($id,$value);
    
    	echo $salida;
    }
    
    
    public function logsAction()
    {
//     	$this->_helper->layout->disableLayout();
    	
    	switch($this->_getParam('type','app'))
    	{
    		case 'app':
    			Break;
    			
    		case 'error':
    			Break;
    			
    		case 'ldap':
    			Break;
    			
    		case 'useraccess':
    			Break;
    	}
    }
    
}

