<div id="createRecurringEvent" title="<?=$this->translate('Crear nueva Programacion')?>" style="display:none">
	<div class="administrarApp">
		<div class="title">Programar evento</div>
		
<?php
$begin = new DateTime( '2007-12-01' );
$end = new DateTime( '2008-01-01' );

$interval = DateInterval::createFromDateString('next Monday');
//$interval = DateInterval::createFromDateString('last thursday of next month');
$period = new DatePeriod($begin, $interval, $end,DatePeriod::EXCLUDE_START_DATE);

foreach ( $period as $dt )
  echo $dt->format( "d/m/Y\n" );
?>
		<div class="description">
			<?=$this->translate('Puede programar la ejecución de un evento desde esta ventana.<br />
			Es posible programar eventos recurrentes desde esta ventana.')?>

		</div>
		
		<div class="content">
			    	<dl class="zend_form">
				        <dt>
				        <label for="add-start-time">Start Time</label>
				        <input type="text" name="ttt"  style="opacity:0;float:right;" id="new_ttt" value="">
				        </dt>
				        <dd>
				        <input type="text" name="start-time" id="new_start-time" />
				        </dd>
				        <dt>
				        <label for="add-end-time">End Time</label>
				        </dt>
				        <dd>
				        <input type="text" name="end-time" id="new_end-time"/>
				        </dd>
				        
				        <dt>
				        <label for="repeats"><?=$this->translate('Repeat')?> </label>
				        </dt>
				        <dd>
				        <input type="checkbox" tabindex="1" name="repeats" id="new_repeats" value="0" />
				        <select name="repeat-options" id="new_repeat-options" style="display:none;width: 80%;float:right; clear:both;">
											<option value=""><?=$this->translate('Select Repeat')?></option>
											<option value="0"><?=$this->translate('Day')?></option>
											<option value="1"><?=$this->translate('Weekly')?></option>
											<option value="2"><?=$this->translate('Monthly')?></option>
											<option value="3"><?=$this->translate('Yearly')?></option>
						</select>
				       </dd>
					</dl>
					<div id="repeat_until_container" style="display:none;width: 90%;text-align:right;margin-top:10px;">
					        <label for="end-repeat"><?=$this->translate('Repeat until:')?></label>
						        <input type="text" name="end-repeat" id="new_end-repeat" />
				    </div>
					
				    <div class="repeat-fieldset-frame">
					    <fieldset id="0_detail" class="repeat-fieldset" style='margin: 0 5px 5px 5px;'>
					   	 	<legend>Day</legend>
					    	<div>
							<label for="0_detail_type"><?=$this->translate('Repetir cada ')?></label>
								<div class="input-append spinner" data-trigger="spinner" id="spinner_0_detail">
									<span class="ui-spinner ui-widget ui-widget-content ui-corner-all">
							            <input class="ui-spinner-input" autocomplete="off" style="width: 40px;" data-max="30" data-min="1" data-step="1" type="text" id="spinner_day" name="0_detail_type" value="1">
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-up ui-corner-tr ui-button ui-widget ui-state-default ui-button-text-only" data-spin="up"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-n">&#9650;</span></span></a>
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-down ui-corner-br ui-button ui-widget ui-state-default ui-button-text-only" data-spin="down"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-s">&#9660;</span></span></a>
									</span>
						        </div>
								<?php echo $this->translate(' day(s).');?>
					    	</div>
					    </fieldset>

					    <fieldset id="1_detail" class="repeat-fieldset" style='margin: 0 5px 5px 5px;'>
					    	<legend><?=$this->translate('Weekly')?></legend>
					    	<div>
							<label for="1_detail_type"><?=$this->translate('Repetir cada ')?></label>
								<div class="input-append spinner" data-trigger="spinner" id="spinner_1_detail">
									<span class="ui-spinner ui-widget ui-widget-content ui-corner-all">
							            <input class="ui-spinner-input" autocomplete="off" style="width: 40px;" data-max="54" data-min="1" data-step="1" type="text" id="spinner_week" name="1_detail_type" value="1">
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-up ui-corner-tr ui-button ui-widget ui-state-default ui-button-text-only" data-spin="up"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-n">&#9650;</span></span></a>
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-down ui-corner-br ui-button ui-widget ui-state-default ui-button-text-only" data-spin="down"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-s">&#9660;</span></span></a>
									</span>
						        </div>
								<?php echo $this->translate(' week(s).');?>
								<br />
					    		<p style="text-align:left;margin-left:5%;">

					    		<span>
					    		<?php 
					    		$timestamp = strtotime('next Monday');
					    		$days = array();
					    		for ($i = 0; $i < 7; $i++) {
					    			$days = strftime('%A', $timestamp);
					    			$timestamp = strtotime('+1 day', $timestamp);
					    			
					    			echo "<label for='$days' style='margin: 0 5px 5px 5px;'>".$this->translate($days)."</label>";
					    			echo "<input type='checkbox' style='margin: 10px 10px 10px 10px;' class='day_checkbox' name='day_checkbox' id='$days' value='$i'/>";
					    			if($i =='3') echo'<br />';
					    		}
					    		
					    		
					    		?></span></p>
					    	</div>
					    </fieldset>
					    
					    <fieldset id="2_detail" class="repeat-fieldset" style='margin: 0 5px 5px 5px;'>
					    	<legend>Monthly</legend>
					    	<div>
					    		<label for="month_count"><?=$this->translate('Repetir cada ')?></label>
								<div class="input-append spinner" data-trigger="spinner" id="spinner_2_detail">
									<span class="ui-spinner ui-widget ui-widget-content ui-corner-all">
							            <input class="ui-spinner-input" autocomplete="off" style="width: 40px;" type="text" id="spinner_month" name="month_count" value="1" data-rule="month">
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-up ui-corner-tr ui-button ui-widget ui-state-default ui-button-text-only" data-spin="up"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-n">&#9650;</span></span></a>
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-down ui-corner-br ui-button ui-widget ui-state-default ui-button-text-only" data-spin="down"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-s">&#9660;</span></span></a>
									</span>
						        </div>
						        <?=$this->translate(' month(s).')?>
								<br />
					    		<span>
					    								    		
						    	<input type="radio" style="margin: 10px;" name="month_type" value="d"/>
								<label for='mount_days' style='margin: 0 5px 5px 5px;'><?=$this->translate('Every Day ')?></label>
								<select name="mount_days" id="new_mount_days">
					    		<?php 
					    		$days = array();
					    		for ($i = 1; $i < 32; $i++) {
					    			echo "<option value='$i'>$i</option>";
					    		}
					    		
					    		
					    		?></select>
					    		<br />
						    	
					    		
					    		</span>
					    	</div>
					    </fieldset>
					    
					    <fieldset id="3_detail" class="repeat-fieldset" style='margin: 0 5px 5px 5px;'>
					    	<legend>Yearly</legend>
					    	<div>
				
							<label for="3_detail_type" style='margin: 0 5px 5px 5px;'><?=$this->translate('Repetir cada ')?></label>
								<div class="input-append spinner" data-trigger="spinner" id="spinner_3_detail">
									<span class="ui-spinner ui-widget ui-widget-content ui-corner-all">
							            <input class="ui-spinner-input" autocomplete="off" style="width: 40px;" data-max="5" data-min="1" data-step="1" type="text" id="spinner_day" name="3_detail_type" value="1">
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-up ui-corner-tr ui-button ui-widget ui-state-default ui-button-text-only" data-spin="up"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-n">&#9650;</span></span></a>
							            	<a href="javascript:;" class="ui-spinner-button ui-spinner-down ui-corner-br ui-button ui-widget ui-state-default ui-button-text-only" data-spin="down"><span class="ui-button-text"><span class="ui-icon ui-icon-triangle-1-s">&#9660;</span></span></a>
									</span>
						        </div>
						        <?=$this->translate(' years(s).')?>
								<br />
								<span>
					    	        <select name="month" id="new_month" style='margin: 0 5px 5px 5px;'>
									<?php 
						    		$timestamp = strtotime('next January');
						    		$month = array();
						    		for ($i = 0; $i < 12; $i++) {
						    			$month = strftime('%B', $timestamp);
						    			$timestamp = strtotime('+1 month', $timestamp);
						    			echo "<option value='$month'>".$this->translate($month)."</option>";
						    		}?>
						    		</select>
						    	</span>
					    	</div>
					    </fieldset>
			    	<dl class="zend_form">					    
						<dt id="Guardar-label">&nbsp;</dt>
						<dd id="Guardar-element">
							<input type="button" name="Create" id="new_create" style="margin: 10px;" value="Guardar">
						</dd>
				   	</dl>
				</div>
		</div>
	</div>
</div>

<script type="text/javascript">

$(function() {

	
	$(".input-append")
	  .spinner('delay', 200) //delay in ms
	  .spinner('changed', function(e, newVal, oldVal){
	    //trigger lazed, depend on delay option.
	  })
	  .spinner('changing', function(e, newVal, oldVal){
	    //trigger immediately
	  });
	
    $("#new_repeats").change(function () {
    	
        $("#new_repeat-options").toggle();
        $('#new_repeat-options').val('');
        $(".repeat-fieldset").css('display','none');
 });
    
    $('#new_create').button({
        icons: {
            primary: "ui-icon-locked"
        }}).click(function () {
        $("#createRecurringEvent").attr('style','display: block !important');
//        alert('Hola');
        $("#new_template_form").submit();
 });

    $("#new_repeat-options").change(function () {
        $(".repeat-fieldset").css('display','none');	
		$('#repeat_until_container').css('display','none');
        name = $('#new_repeat-options option:selected').val();
        //alert(name);
    	if(name=='')
    	{
        	//alert(name+"_detail");
    	}
    	else
    	{
    		$('input[name="0_detail_type"]:checked').attr("data-container",'hola');
    		check_id = $('#new_key_recur').val();
      		 check_value = $('#new_value_recur').val();
    		//check_value = $('input[name="'+name+'_detail_type"]:checked').next().val();
    		//check_id = $('input[name="'+name+'_detail_type"]:checked').next().attr("data-name");
    		$('#'+name+'_detail').css('display','block');
    		$('#repeat_until_container').css('display','block');
    	}
 });
    
    $(".day_checkbox").click(function () {
    
    	var n =  $('input[name="day_checkbox"]:checked').length;
    	alert(n + (n === 1 ? " is" : " are") + " checked!");
    	});
    	
	$("#new_start-time").timepicker({
		showButtonPanel: true,
		stepMinute: 5,
		timeOnlyTitle: 'Seleccionar hora inicio',
		timeText: 'Hora',
		hourText: 'Horas',
		minuteText: 'Minutos',
		secondText: 'Segundos',
		millisecText: 'Milisegundos',
		timezoneText: 'Zona horaria',
		currentText: 'Ahora',
		closeText: 'Cerrar',
		timeFormat: 'hh:mm',
		amNames: ['a.m.', 'AM', 'A'],
		pmNames: ['p.m.', 'PM', 'P'],
		ampm: false,
		firstDay: 1,
		dateFormat: 'dd-mm-yy'
	});

	$("#new_end-repeat").datepicker({
		showButtonPanel: true,
		stepMinute: 5,
		timeOnlyTitle: 'Seleccionar hora inicio',
		timeText: 'Hora',
		hourText: 'Horas',
		minuteText: 'Minutos',
		secondText: 'Segundos',
		millisecText: 'Milisegundos',
		timezoneText: 'Zona horaria',
		currentText: 'Ahora',
		closeText: 'Cerrar',
		timeFormat: 'hh:mm',
		amNames: ['a.m.', 'AM', 'A'],
		pmNames: ['p.m.', 'PM', 'P'],
		ampm: false,
		firstDay: 1,
		dateFormat: 'dd-mm-yy'
	});
	
	
	$("#new_end-time").timepicker({
		showButtonPanel: true,
		stepMinute: 5,
		timeOnlyTitle: 'Seleccionar fecha y hora fin',
		timeText: 'Hora',
		hourText: 'Horas',
		minuteText: 'Minutos',
		secondText: 'Segundos',
		millisecText: 'Milisegundos',
		timezoneText: 'Zona horaria',
		currentText: 'Ahora',
		closeText: 'Cerrar',
		timeFormat: 'hh:mm',
		amNames: ['a.m.', 'AM', 'A'],
		pmNames: ['p.m.', 'PM', 'P'],
		ampm: false,
		firstDay: 1,
		dateFormat: 'dd-mm-yy'
	});

});			


</script>