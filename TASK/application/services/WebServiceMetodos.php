<?php 

class Application_Service_WebServiceMetodos
{
	
	/**
	 * Este método crea una nueva tarea.
	 * 
	 * El parámetro $origen es el nombre de la app que envía los datos . Ej: Projects.
	 * 
	 * El parámetro $datos es un array con los siguientes campos: 
	 * 
	 * Obligatorios:
	 *  - Campo; Tipo; Descricpión
	 *  - idOrigen; Int; Identificador de la tarea en origen;
	 *  - title; String; Título de la tarea;
	 *  - description; String; Descripción de la tarea;
	 *  - OU_ID; String; OU_ID al que ha sido asignada;
	 *  - creada; DATETIME MySQL format; Fecha de creación;
	 * 
	 * Opcionales:
	 *  - Campo; Tipo; Descricpión
	 *  - username; String; Nombre de usuario al que ha sido asignada la tarea, si se especifica es obligatorio proporcionar el WiW;
	 *  - Emp_WIW_Ident; Int; WiW del usuario al que ha sido asignada la tarea, si se especifica es obligatorio proporcionar el username;
	 *  - limite; DATETIME MySQL format; Fecha límite.;
	 *  - finalizada; DATETIME MySQL format; Fecha de finalización;
	 *  - minimo; TIME MySQL format; Tiempo requerido para realizar la tarea;
	 * 
	 * 
	 * @param string $origen
	 * @param array $datos
	 * @return array
	 */
	public function newTask($origen,$datos)
	{
		
	}

	/**
	 * Este método edita una tarea existente.
	 *
	 * El parámetro $origen es el nombre de la app que edita los datos . Ej: Projects.
	 *
	 * El parámetro $datos es un array con los siguientes campos:
	 *
	 * Obligatorios:
	 *  - Campo; Tipo; Descricpión
	 *  - idOrigen; Int; Identificador de la tarea en origen;
	 *  
	 * 
	 * Opcionales:
	 *  - Campo; Tipo; Descricpión
	 *  - title; String; Título de la tarea;
	 *  - description; String; Descripción de la tarea;
	 *  - OU_ID; String; OU_ID al que ha sido asignada;
	 *  - creada; DATETIME MySQL format; Fecha de creación;
	 *  - limite; DATETIME MySQL format; Fecha límite.;
	 *  - finalizada; DATETIME MySQL format; Fecha de finalización;
	 *  - minimo; TIME MySQL format; Tiempo requerido para realizar la tarea;
	 *
	 *
	 * @param string $origen
	 * @param array $datos
	 * @return array
	 */
	public function editTask($origen,$datos)
	{
	
	}
}

?>