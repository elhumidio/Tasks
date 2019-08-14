<?php 
/**
 * Action Helper for finding days in a month
 */
 
class Zend_Controller_Action_Helper_Myrecur extends Zend_Controller_Action_Helper_Abstract
{
    public $recur;

    public function __construct()
    {

    }

    public function getrecur($paramt)
    {       
    	$step = array_filter(explode(',',$paramt['value_recur']));
      	$unit =$paramt['key_recur'];
        $pas = $paramt['value1_recur'];
    	if(isset($paramt['until'])){
    	    $end =(new DateTime( $paramt['until'].'+1 day' ) > new DateTime( "now +1 month" ))?new DateTime( "now +1 month" ):new DateTime( $paramt['until'].'+1 day' );  	    	
        } else {
    		$end = new DateTime( "now +1 month" );
    	}
    	$begin = new DateTime( $paramt['begin']);
        //print_r($begin);
        if (strpos($paramt['begin'],'/') !== false) {
            $check_start = new DateTime();   
            if($begin->format('d') > $check_start->format('d') )  
            {
                $begin->modify("-$pas month");
            } else {$begin->modify("-$pas-1 month");}
        } else {
                
        }
        //print_r($begin);    	

		foreach ( $step as $st )
		{
			$interval = DateInterval::createFromDateString("{$st} {$unit}");
            // $interval = DateInterval::createFromDateString("this +2 month");
			//$interval = DateInterval::createFromDateString("{$st} {$unit} every 1 weeks");
			//$interval = DateInterval::createFromDateString('last thursday of next month');
			$period = new DatePeriod($begin, $interval, $end, DatePeriod::EXCLUDE_START_DATE);
	
			foreach ( $period as $dt )
			{
			  $da_arr[] = $dt->format( "Y/m/d\n" );
			}
    	}
    	return $da_arr;
   }

	

    public function direct($parametros)
    {
        return $this->getrecur($parametros);
    }
}