<?php

class CambioturnoController extends Zend_Controller_Action
{
    
    protected $baseurl;
    protected $db;
    protected $logapp;

    protected $model;
    
    private $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
    private $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    
    public function init()
    {
        $this->_helper->layout->setLayout('general');
        
        $this->baseurl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        $this->db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $this->logapp = $logapp = new Application_Plugin_SystemLog();
        
        // Uploads
        $this->view->headLink()->appendStylesheet ( '/estilos/blueimp-jQuery-File-Upload-02efca0/css/jquery.fileupload-ui.css');
        $this->view->headLink()->appendStylesheet ( '/estilos/css/upload.css' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/vendor/jquery.ui.widget.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.iframe-transport.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.fileupload.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/tmpl.min.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.fileupload-fp.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.fileupload-ui.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/locale.js' );
//      $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/main.js' );

        $this->view->headScript()->appendFile ( $this->baseurl.'/estilos/js/jquery.jui_dropdown.min.js' );
        $this->view->headLink()->appendStylesheet ( $this->baseurl.'/estilos/css/jquery.jui_dropdown.css' );
        
        $this->view->headScript()->appendFile ( $this->baseurl.'/estilos/js/cambioturno.js' );
        $this->view->headLink()->appendStylesheet ( $this->baseurl.'/estilos/css/cambioturno.css' );

        $this->model = new Application_Model_CambioTurno_CambioTurno($this->view->user);
        
    }

    
    public function debug($valores,$die=true)
    {
        echo '<pre class="clear">';
        print_r($valores);
        echo '</pre>';
        if($die) die();
    }
    
    
	
	/**
	* Funcion que guarda el log los errores producidos en JavaScript
	*
	*/	
    public function logerrorAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
         
        $tipo = $this->_getParam('tipo');
        $origen = $this->_getParam('origen');
        $error = $this->_getParam('error');
         
         
        echo $error;
    
        $log = new Application_Plugin_LogError();
        $log->setLog($tipo, 3, array( 'type' => "Application error", 'exception' => $error, 'controller'=>null, 'action'=>null, 'parametros'=>$origen, 'referer'=>((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:NULL),'user_id'=>((isset($this->view->user->ID))?$this->view->user->ID:NULL), 'username'=>((isset($this->view->user->username))?$this->view->user->username:NULL)));
         
    }
	  
    // ###########################################################################################################################    
    
    public function indexAction(){
        
//        $model = new Application_Model_DbTable_CambioTurno_CambioTurno();
//        $model->setInit($this->view->user);
    
//        $res = $model->terminarEntrada(142);
//        var_dump($res);
//        die;
        
        
        
        $fechaFormateada =  date('j')." ".$this->meses[date('n')-1]. " ".date('Y') ;
        
        $this->view->fecha = date('Y-m-d');
        $this->view->fechaFormateada = $fechaFormateada;
        $this->view->diaSemana = $this->dias[date('w')];
        
        $div = $this->model->getEntradasDia($this->view->user->OU_ID, date('Y-m-d'));
        $this->view->contenido = $div;
        
    }
    
    public function llenatablaAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $fecha = $this->_getParam('fecha');
        $grupo = $this->_getParam('grupo');
        $busqueda = $this->_getParam('busqueda');
        
        
        $div = $this->model->getEntradasDia($grupo, $fecha, $busqueda);
        $fechaFormateada =  date('j', strtotime($fecha))." ".$this->meses[date('n', strtotime($fecha))-1]. " ".date('Y',strtotime($fecha)) ;
         
        //echo $div;
        echo json_encode(array('div'=>$div, 'fecha'=>$fechaFormateada, 'dia'=>$this->dias[date('w', strtotime($fecha))]));
    
    }
    
    
    public function entradaAction(){
        
        $this->_helper->layout->disableLayout();
        $opcion = $this->_getParam('opcion');

        $this->view->opcion = $opcion;
        
    }

    public function procesaAction(){
        
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $titulo = $this->_getParam('titulo');
        $descripcion = $this->_getParam('descripcion');
        $pendiente = $this->_getParam('pendiente');
        $critico = $this->_getParam('critico');
        $grupo = $this->_getParam('grupo');
        
        $params = array('titulo'=>$titulo, 'descripcion'=>$descripcion, 'pendiente'=>$pendiente, 'grupo'=>$grupo, 'critico'=>$critico);
        
        $this->model->procesaEntradas($params);

        echo json_encode(array('res'=>'1'));
        
    }
    
    public function finalizaentradaAction(){
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $id = $this->_getParam('id');
        $fecha = $this->_getParam('fecha');
        
        $res = $this->model->finalizaEntrada($id, $fecha);
        
        echo json_encode(array('res'=>$res));
        
    }
    
    public function pendienteentradaAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $id = $this->_getParam('id');
        $res = $this->model->pendienteEntrada($id);
    
        echo json_encode(array('res'=>$res));
    
    }

    public function criticoentradaAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $id = $this->_getParam('id');
//file_put_contents('test.txt', '1. criticoentradaAction: id='.$id.PHP_EOL, FILE_APPEND);
        $res = $this->model->criticoEntrada($id);
    
        echo json_encode(array('res'=>$res));
    
    }
    
    public function eliminaentradaAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $id = $this->_getParam('id');
        $res = $this->model->eliminaEntrada($id);
    
        echo json_encode(array('res'=>$res));
    
    }

    public function historialAction(){
        
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');

        $res = $this->model->getHistorialEntrada($id);
        $this->view->data = $res;
        
    }

    public function editaentradaAction(){

        $this->_helper->layout->disableLayout();
         
        $id = $this->_getParam('id');
        $params = array('id'=>$id);
                
        $this->view->data = $this->model->getEntradaEdicion($params);
        
    }
    
    public function procesaeditentradaAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $id = $this->_getParam('id');
        $titulo = $this->_getParam('titulo');
        $descripcion = $this->_getParam('descripcion');
        $pendiente = $this->_getParam('pendiente');
        $critico = $this->_getParam('critico');
        $turno = $this->_getParam('turno');
        $fecha = $this->_getParam('fecha');
        
        $params = array('id'=>$id, 'params'=>array('titulo'=>$titulo, 'descripcion'=>$descripcion, 'pendiente'=>$pendiente, 'critico'=>$critico, 'turno'=>$turno, 'fecha'=>$fecha));
        $res = $this->model->procesaEditEntrada($params);
        
        echo json_encode(array('res'=>'1', 'data'=>$res));
    
    }

    public function busquedaAction(){
        
        $this->_helper->layout->disableLayout();
        $textoBusqueda = $this->_getParam('textoBusqueda');
        $grupo = $this->_getParam('grupo');

        $entradas = $this->model->getEntradasBusqueda($textoBusqueda, $grupo);
        $this->view->entradasBusqueda = $entradas;        
        
    }
        
    public function papeleraAction(){
        $this->_helper->layout->disableLayout();
        $this->view->papelera = $this->model->getPapelera();
    }    
    
    public function historialborradoAction(){
    
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
    
        $res = $this->model->getHistorialEntradaBorrada($id);
        $this->view->data = $res;
    
    }
    
    public function terminarAction(){
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    
        $id = $this->_getParam('id');
        $res = $this->model->cambiaTerminarEntrada($id);
    
        echo json_encode(array('res'=>$res));
    
    }
    
    /**
     * Recarga la tabla de titulos
     */
    public function refreshtitulosAction()
    {
        $titulos = new Application_Model_CambioTurno_CambioTurno($this->view->user);
        $this->_helper->json->sendJson($titulos->getTitulos());
    
    }
    
    
    /**
     * Recupera un registro de la papelera
     */
    public function recuperarregistropapeleraAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
        $this->model->recuperaEntrada($id);
        $this->_helper->json->sendJson("OK");
    }
    
    
    
}


