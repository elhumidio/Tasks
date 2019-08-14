<?php

class Checklist_AjaxController extends Zend_Controller_Action
{
    	protected $checklist;
    	protected $planificador;
    
    	public function init()
    	{
    		$this->_helper->layout->disableLayout();
    		$this->_helper->viewRenderer->setNoRender();
    		// Importante pasar los parámetros
    		$this->checklist = new Application_Model_Calendar_Calendar($this->_getAllParams());
    		$this->planificador = new Application_Model_Calendar_Planificador($this->_getAllParams());
    	}
    
    	
    public function indexAction()
    {
    	
    }
    
    /**
     * Devuelve las tareas asignadas
     */
    public function gettaskAction()
    {
    	$this->view->TareasExternas()->Get($this->_getParam('idtarea'));
    }
    
    /**
     * Devuelve las tareas asignadas
     */
    public function getbitacorataskAction()
    {
    	$this->view->TareasExternas()->GetBitacora();
    }
    
    
    /**
     * Imprescindible para el funcionamiento del Plugin Token
     */
    public function csrfForbiddenAction()
    {
    	$salida = array('error'=>array('Token'=>$this->view->translate('Fallo de seguridad, vuelva a intentarlo. Si el problema persiste contacte con automation@t-systems.es')));
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Desasigna una tarea de un usuario
     */
    public function deletetareaAction()
    {
    	$salida = $this->checklist->DesasignarTarea();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Asigna una tarea al usuario
     */
    public function asignarmeAction()
    {
    	$salida = $this->checklist->AsignarTarea();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Devuelve los eventos para el calendario
     */
    public function eventsAction()
    {
    	$user = ($this->_getParam('users'))?json_decode($this->_getParam('users')):false;
    	$salida = $this->checklist->GetEvents($user);
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Devuelve los tags para los eventos
     */
    public function tagsAction()
    {
    	$term = ($this->_getParam('term'))?$this->_getParam('term'):'';
    	$salida = $this->checklist->GetTags($term);
    	$this->_helper->json->sendJson($salida);
    }
    
    public function addtagsAction()
    {
    	$term = ($this->_getParam('term'))?$this->_getParam('term'):'';
    	$salida = $this->checklist->InsertTags($term);
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Borra un evento del calendario
     */
    public function deleteeventsAction()
    {
    	$salida = $this->checklist->DeleteEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    
    /**
     * Acción que guarda el movimiento de un evento en el calendario
     */
    public function moveAction()
    {
    	$salida = $this->checklist->MoveEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción para crear un nuevo evento
     */
    public function newAction()
    {
    	$salida = $this->checklist->NewEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción dedicada al la gestión de los comentarios
     */
    public function comentariosAction()
    {
    	switch($this->_getParam('type'))
    	{
    		case 'add': $salida = $this->checklist->addComentario();Break;
    		case 'del': $salida = $this->checklist->delComentario();Break;
    		default: $salida = $this->checklist->getComentario();Break;
    	}
    	
    	$this->_helper->json->sendJson($salida);
    	//echo $salida;
    }
    
    /**
     * Accion que devuelve los miembros del grupo (Tools, wintel, etc..) del usuario
     */
    public function getgroupmembersAction()
    {
    	$salida = $this->planificador->GetGroupMembers();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción de Planificador, devuelve:
     * 		Lista de miembros para cabecera de tabla.
     * 		Lista de origienes de datos para cabecera de tabla.
     * 		Lista de tareas.
     * 		Stats
     */
    public function gestplanificadorAction()
    {
    	$this->getResponse()->clearHeaders()->setHttpResponseCode(200)->sendResponse(); // Acceso no permitido
    	
    	$salida = array();
    	
    	$cache = Zend_Registry::get ( 'Cache' );
    	
    	if( ($salida['Memberlist'] = $cache->load(md5($this->planificador->userData->OU_ID.'_GetGroupMembers'))) === false )
    	{
    		$salida['Memberlist'] = $this->planificador->GetGroupMembers();
    		$cache->save($salida['Memberlist'], md5($this->planificador->userData->OU_ID.'_GetGroupMembers'));
    	}
    	
    	//$salida['Memberlist'] = $this->planificador->GetGroupMembers();
    	$salida['OrigenesList'] = $this->planificador->GetOrigenList();
    	$salida['TareasList'] = $this->planificador->GetTaskMembers();
    	$salida['Stats'] = $this->planificador->GetTaskStats();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción de Subgrupo, devuelve:
     * 		Subgrupos (Ovo, Remedy, etc...) del grupo del usuario
     * 		Usuarios
     * 		Usuarios Miembros
     * 		Stats
     * 		
     */
    public function gestorgroupAction()
    {
    	$salida = array();
    	$salida['subgrupos'] = $this->planificador->GetSubGroup();
		$salida['miembros'] = $this->planificador->GetSubGroupMembers();
		
		$cache = Zend_Registry::get ( 'Cache' );
		if( ($salida['usuarios'] = $cache->load(md5($this->planificador->userData->OU_ID.'_GetGroupMembers'))) === false )
	    {
	    	$salida['usuarios'] = $this->planificador->GetGroupMembers();
    		$cache->save($salida['usuarios'], md5($this->planificador->userData->OU_ID.'_GetGroupMembers'));
    	}
    	
    	$this->_helper->json->sendJson($salida);
    }
	
	    /**
     * Acción dedicada al la gestión de los subgrupos
     */
    public function subgruposAction()
    {
    	switch($this->_getParam('type'))
    	{
    		case 'set': $salida = $this->planificador->SetSubGroup(); Break;
    		case 'edit': $salida = $this->planificador->EditSubGroup(); Break;
    		case 'delete': $salida = $this->planificador->DeleteSubGroup(); Break;
    		case 'empty': $salida = $this->planificador->EmptySubGroup(); Break;
    		
    		case 'relusers': $salida = $this->planificador->RelUserSubGroup(); Break;
    		case 'deleteusers': $salida = $this->planificador->DeleteUserFromSubGroup(); Break;
    		case 'users': $salida = $this->checklist->getSubgroupUsers();Break;
    		    
    		default: $salida = array(); Break;
    	}
    	$this->_helper->json->sendJson($salida);
    }
    /**
     * Añade un Subgrupo
     */
    /*public function setsubgroupAction()
    {
    	$salida = $this->planificador->SetSubGroup();
    	$this->_helper->json->sendJson($salida);
    }*/
    
    /**
     * edita un Subgrupo
     
    public function editsubgroupAction()
    {
    	$salida = $this->planificador->EditSubGroup();
    	$this->_helper->json->sendJson($salida);
    }*/
    
    /**
     * Elimina un Subgrupo
     
    public function deletesubgroupAction()
    {
    	$salida = $this->planificador->DeleteSubGroup();
    	$this->_helper->json->sendJson($salida);
    }*/
    
    /**
     * Relaciona un usuario con un grupo
     
    public function relusergroupAction()
    {
    	$salida = $this->planificador->RelUserSubGroup();
    	$this->_helper->json->sendJson($salida);
    }
   
    public function deleteuserfromsubgroupAction()
    {
    	$salida = $this->planificador->DeleteUserFromSubGroup();
    	$this->_helper->json->sendJson($salida);
    }
    
    public function emptysubgroupAction()
    {
    	$salida = $this->planificador->EmptySubGroup();
    	$this->_helper->json->sendJson($salida);
    }*/
}

    