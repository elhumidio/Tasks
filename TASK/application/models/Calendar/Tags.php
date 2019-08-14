<?php
/**
 * 
 * @author Toni Ibáñez Justicia - antonio.ibanez@t-systems.com
 * @version 1.0 - 11/05/2015
 *
 */

class Application_Model_Calendar_Tags
{
    /**
     * Variable que contiene un objeto con los datos del usuario logueado
     * @var object
     */
    private $userData;
    
    /**
     * Variable que contiene los parámetros enviados
     */
    private $params;
    
    /**
     * Contiene el log de evento
     * @var object
     */
    private $eLog;
    private $tLog;
    
    private $Notificaciones;
    
    
    public function __construct($params=array())
    {
        $this->params = $params;
        
        $storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
        $namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
        $this->userData = $storage->read($namespace);
        
        $this->eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $this->eLog->__set('username',$this->userData->username);
        $this->eLog->__set('OU_ID',$this->userData->OU_ID);
        
        $this->tLog = new Application_Model_DbTable_Calendar_TareasLog();
        $this->tLog->__set('username',$this->userData->username);
        $this->tLog->__set('OU_ID',$this->userData->OU_ID);
    }
    
     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function GetUserTags()
    {
        $fil_centro = new Application_Model_DbTable_Calendar_Eventos();
        
        //$userTags[] = array('name'=>$this->userData->OU_ID, 'filter' =>'c.OU_ID');
        $userTags['turno']['id'] = 'a.turno';
        $userTags['turno']['description'] = 'Add Shifts Filter';
        $userTags['turno']['data_used'][] = self::get_Shifts();
        $userTags['turno']['data_aval'] = self::filter_Shifts($userTags['turno']['data_used'][0]['name']);
        $userTags['centro']['id'] = 'a.centro';
        $userTags['centro']['description'] = 'Add Location Filter';        
        $userTags['centro']['data_used'][] = array('name'=>$this->userData->centro);
        $userTags['centro']['data_aval'] = $fil_centro->GetLocation($userTags['centro']['data_used'][0]['name']);
        $userTags['group']['id'] = 'a.group';
        $userTags['group']['description'] = 'Add Group Filter';        
        $userTags['group']['data_aval'] = $fil_centro->GetGroups();
        $userTags['cliente']['id'] = 'a.cliente';
        $userTags['cliente']['description'] = 'Add Client Filter';        
        $userTags['cliente']['data_aval'] = $fil_centro->GetClients();
        return $userTags;
    }

     /**
     * Devuelve los tags por defecto que se aplican al usuario, para el filtro del Calendario.
     * @return array
     */
    public function GetValidTags()
    {
        $ValidTags = array('Evening','Morning','Night','Checklist','Follow-Up');
        return $ValidTags;
    }
    
    public function GetTags($term)
    {
        $TagsDb = new Application_Model_DbTable_Calendar_Tags();
    
        $select = $TagsDb->select()
        ->from(array('a'=>$TagsDb->__get('_name')),array('id','tag'))
        ->where('tag LIKE ?',"%$term%");
    
        $Tags = $TagsDb->fetchAll($select)->toArray();
    
        //$Tags = self::getEventoptions($Tags);
            //if(!is_null($this->params['idtarea'])) $this->tLog->log($this->params['idtarea'],'Tarea programada.');
        return $Tags;
    }
    
    /**
     *Inserta nuevos tags a la BBDD
     * @return json
     */
    public function InsertTags($term)
    {
        $TagsDb = new Application_Model_DbTable_Calendar_Tags();
        try 
        {
            $result = $TagsDb->insert(array('tag'=>$term));
        }
     catch (Zend_Db_Exception $e) {
        echo('Fail');
        echo($result);
  // ignored 
        }
        echo $result;
                
    }
    

    
    
    /**
     * get the current Shifts based in current hour
     *
     * @param string The string to convert
     * @return string The converted string
     */
    function get_Shifts()
    {
        /*if (date('H') >= 6 && date("H") < 14) {
            $hour = 'Morning';
        } else if (date('H') >= 14 && date("H") < 22) {
            $hour = 'Evening';
        } else {
            $hour = 'Night';
        }*/


        if (date('H') >= 7 && date("H") < 15) {
            $hour = 'Morning';
        } else if (date('H') >= 15 && date("H") < 23) {
            $hour = 'Evening';
        } else {
            $hour = 'Night';
        }
        return array('name'=>$hour);
    }
    
     function filter_Shifts($shift)
    {
        $shifts = array('Morning','Evening','Night');
        if(($key = array_search($shift, $shifts)) !== false) {
            unset($shifts[$key]);
        }
        foreach ($shifts as $value) {
            $res[] =array('name' => $value);            
        }
        return $res;
    }       
    
    
    
    /**
     * Convert BR tags to nl
     *
     * @param string The string to convert
     * @return string The converted string
     */
    function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    
    public function __set($propiedad,$valor) {$this->$propiedad=$valor;}
    public function __get($propiedad) {return $this->$propiedad;}
    public function __isset($propiedad) {return isset($this->$propiedad);}
    public function __unset($propiedad) {unset ($this->$propiedad);}
}