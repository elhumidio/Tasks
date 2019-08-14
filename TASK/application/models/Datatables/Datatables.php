<?php
/**
 * Clase que contiene los métodos utilizados por cada una de las vistas para obtener datos de Data Tables
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 2.2 - 02/01/2013
 *
 */

class Application_Model_Datatables_Datatables extends Application_Model_Datatables_Abstract
{	
	
	/**
	 * Función que devuelve el listado de usuarios para /admin/usuarios
	 * 
	 * @return string
	 */
	static public function generateUserTable()
	{
		return parent::process('user', array('ID','username','country','unserialize:role','created_at','last_access'), 'ID', false, 'user_');
	}
	
	
	/**
	 * Función que devuelve el listado de recursos para /admin/permisos_y_roles
	 *
	 * @return string
	 */
	static public function generateResursosTable()
	{
		return parent::process('app_recursos', array('ID','module','controller','action'), 'ID', false, 'recurso_');
	}
	
	
	/**
	 * Función que devuelve el listado de roles para /admin/permisos_y_roles
	 *
	 * @return string
	 */
	static public function generateRolesTable()
	{
		return parent::process('app_roles', array('ID','name','hereda','iphoneStyleRoles:estado'), 'ID', false, 'roles_');
	}
	
	
	/**
	 * Función que devuelve el listado de modulos para /admin/general
	 *
	 * @return string
	 */
	static public function generateModulesTable()
	{
		return parent::process('app_modulos', array('ID','appName','dominio','url','country','sistema_easya','beta','new'), 'ID', false, 'modulo_');
	}
	
	/**
	 * Función que devuelve el listado de modulos para /admin/general
	 *
	 * @return string
	 */
	static public function generatetemplatesTable()
	{
		//return parent::process('cal_tareas', array('id',  'title',  'description',  'OU_ID',  'origen',  'estado',  'creada',  'limite',  'asignada',  'programada',  'finalizada',  'minimo',  'maximo',  'template',  'programacion', 'rgroup'), 'id', false, 'template_');
		//return parent::process('cal_tareas_view', array('img','id', 'group', 'turno', 'centro', 'description', 'OU_ID', 'Designado', 'start', 'end', 'title', 'environment', 'title', 'status', 'client', 'environment', 'origen', 'Fecha Log', 'User Log', 'Message Log','group'), 'id', false, 'template_');
		//return parent::process('cal_tareas_view', array('img', 'id', 'group', 'turno', 'centro', 'description', 'OU_ID', 'Designado', 'start', 'end', 'title', 'environment', 'title', 'status', 'client', 'environment', 'origen', 'Fecha Log', 'User Log', 'Message Log','group','creada','owner','rgroup','type','programacion', 'open-close-ticket', 'minutes-close-ticket'), 'id', "origen='Checklist'", 'template_');
	    return parent::process('cal_tareas_view', array('img', 'id', 'group', 'turno', 'centro', 'description', 'OU_ID', 'Designado', 'start', 'end', 'title', 'environment', 'title', 'status', 'client_nombre', 'environment', 'origen', 'Fecha Log', 'User Log', 'Message Log','group','creada','owner','rgroup','type','programacion', 'open-close-ticket', 'minutes-close-ticket', 'client'), 'id', "origen='Checklist'", 'template_');
	}
    
    static public function generatejtemplatesTable()
    {
//          return parent::process('cal_tareas_view', array('img', 'id', 'group', 'turno', 'centro', 'description', 'OU_ID', 'Designado', 'start', 'end', 'title', 'environment', 'title', 'status', 'client', 'environment', 'origen', 'Fecha Log', 'User Log', 'Message Log','group','creada','owner','rgroup','type','programacion'), 'id', "origen='Journal'", 'template_');
         return parent::process('cal_tareas_view', array('img', 'id', 'group', 'turno', 'centro', 'description', 'OU_ID', 'Designado', 'start', 'end', 'title', 'environment', 'title', 'status', 'client', 'environment', 'origen', 'Fecha Log', 'User Log', 'Message Log','group','creada','owner','rgroup','type','programacion', 'open-close-ticket', 'minutes-close-ticket', 'client','refer','tasks'), 'id', "origen='Journal'", 'template_');
    }
	
	static public function generateEventsTable()
	{
// 		return parent::process('cal_eventos_view', array('img', 'id',  'idTarea', 'limite', 'title',  'description', 'fstart', 'fend', 'origen', 'status', 'refer', 'turno', 'centro', 'cliente', 'group','rgroup', 'OU_ID', 'Designado','environment', 'type','Fecha Log', 'User Log', 'Message Log' ), 'id', "origen='Checklist'", 'event_');
		return parent::process('cal_eventos_view', array('img', 'id',  'idTarea', 'limite', 'title',  'description', 'fstart', 'fend', 'origen', 'status', 'refer', 'turno', 'centro', 'cliente_nombre', 'group','rgroup', 'OU_ID', 'Designado','environment', 'type',/*'Fecha Log', 'User Log', 'Message Log', */'open-close-ticket', 'minutes-close-ticket', 'cliente','usuario' ), 'id', "origen='Checklist'", 'event_');
		
	}
    
    static public function generateEventsJournalTable()
    {
		//return '{"sEcho":0,"iTotalRecords":"0","iTotalDisplayRecords":"0","aaData":[]}';
        return parent::process('cal_eventos_view', array('img', 'id',  'idTarea', 'limite', 'title',  'description', 'fstart', 'fend', 'origen', 'status', 'refer', 'turno', 'centro', 'cliente', 'group','rgroup', 'OU_ID', 'Designado','environment', 'type',/*'Fecha Log', 'User Log', 'Message Log',*/ 'params','creada','usuario' ), 'id', "origen='Journal'", 'event_');
    }
    /*    static public function generateEventsJournalTableNoDelete()
    {
        //return '{"sEcho":0,"iTotalRecords":"0","iTotalDisplayRecords":"0","aaData":[]}';
        return parent::process('cal_eventos_view_nodelete', array('img', 'id',  'idTarea', 'limite', 'title',  'description', 'fstart', 'fend', 'origen', 'status', 'refer', 'turno', 'centro', 'cliente', 'group','rgroup', 'OU_ID', 'Designado','environment', 'type','Fecha Log', 'User Log', 'Message Log' ), 'id', "origen='Journal'", 'event_');
    }*/
	    static public function generateEventsJournalTableNoDelete()
    {
        //return '{"sEcho":0,"iTotalRecords":"0","iTotalDisplayRecords":"0","aaData":[]}';
        return parent::process('cal_eventos_view_nodelete', array('img', 'id',  'idTarea', 'limite', 'title',  'description', 'fstart', 'fend', 'origen', 'status', 'refer', 'turno', 'centro', 'cliente', 'group','rgroup', 'OU_ID', 'Designado','environment', 'type','params'/*,'Fecha Log', 'User Log', 'Message Log'*/ ), 'id', "origen='Journal'", 'event_');
    }
}