<?php

/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Navegacion
 * @filesource  application/plugins/Navegacion.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.Navegacion = "Application_Plugin_Navegacion"
 */
class Application_Plugin_Navegacion extends Zend_Controller_Plugin_Abstract
{

    public function run ($db)
    {
        $var = '<?xml version="1.0" encoding="UTF-8" ?>
		<configdata>
		    <nav>
		        <label>Home</label>
		        <controller>index</controller>
		        <action>index</action>
		        <pages>
		            <cambioturno>
		                <label>Cambio de Turno</label>
		                <controller>cambioturno</controller>
		                <action>index</action>
		            </cambioturno>		        
            
		        	<calendar>
		                <label>Eventos</label>
		                <module>calendar</module>
		                <controller>index</controller>
		                <action>index</action>
		                <pages>
                            <edit>
                                <label>Administrar Eventos</label>
                                <module>calendar</module>
                                <controller>edit</controller>
                                <action>index</action>
                            </edit>
                            <seguimiento>
                                <label>Seguimiento</label>
                                <module>calendar</module>
                                <controller>edit</controller>
                                <action>seguimiento</action>
                            </seguimiento>
                        </pages>
		            </calendar>
		          
		            	            <journal>
		                <label>Journal</label>
		                <module>calendar</module>
		                <controller>index</controller>
		                <action>ijournal</action>
		               
		            </journal>
					 <jeventsnew>
		                <label>Búsqueda en Journal (Bitácora)</label>
		                <module>calendar</module>
		                <controller>index</controller>
		                <action>jeventsnew</action>
		               
		            </jeventsnew>
					  <admincligrup>
		                <label>Administrar Clientes y Grupos</label>
		                <module>calendar</module>
		                <controller>index</controller>
		                <action>admincligrup</action>
		               
		            </admincligrup>
		             
		            <checklist>
		                <label>Checklist</label>
		                <module>checklist</module>
		                <controller>index</controller>
		                <action>index</action>
		            
		            </checklist>
	
		            
		       		<error>
		                <label>Error</label>
		                <controller>error</controller>
		                <action>index</action>
		            </error>
		            
		        	<admin>
		                <label>Administrar aplicacion</label>
		                <controller>admin</controller>
		                <action>index</action>
		                <pages>
		                
		                	<delegation>
		                		<label>Delegaciones</label>
				                <controller>admin</controller>
				                <action>delegations</action>
		                	</delegation>
		                	
		                	<sess>
		                		<label>Sesiones activas</label>
				                <controller>admin</controller>
				                <action>sessions</action>
		                	</sess>
		                	
		                	<gral>
		                		<label>Configuracion general</label>
				                <controller>admin</controller>
				                <action>general</action>
		                	</gral>
		                	
		                	<roles>
		                		<label>Roles</label>
				                <controller>admin</controller>
				                <action>roles</action>
		                	</roles>
		                	
		                	<recursos>
		                		<label>Recursos</label>
				                <controller>admin</controller>
				                <action>recursos</action>
		                	</recursos>
		                	
		                	<permisos>
		                		<label>Permisos</label>
				                <controller>admin</controller>
				                <action>permisos</action>
		                	</permisos>
		                	
		                	<permisos_y_roles>
		                		<label>Permisos y roles de acceso</label>
				                <controller>admin</controller>
				                <action>permisos_y_roles</action>
		                	</permisos_y_roles>
		                	
		                	<grupos>
		                		<label>Grupos</label>
				                <controller>admin</controller>
				                <action>grupos</action>
		                	</grupos>
		                	
		                	<user>
		                		<label>Usuarios</label>
				                <controller>admin</controller>
				                <action>usuarios</action>
		                	</user>
		                	
		                </pages>
		            </admin>
				</pages>
				
		    </nav>
		</configdata>';
        
        return $var;
    }
}