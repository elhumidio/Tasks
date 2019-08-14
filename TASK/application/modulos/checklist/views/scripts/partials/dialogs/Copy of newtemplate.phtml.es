<div id="createNewTemplate" title="<?=$this->translate('Create new Template')?>" style="display:none">

	<div class="administrarApp">
		<div class="title"><?=$this->translate('Create new Template')?></div>
		<div class="description">
			<?=$this->translate('You can create and manage templates from this tab.<br />
			The templates are used to create the checklist events.')?>

		</div>
		
		
		
		<div class="content">
			<form id="new_template_form" enctype='application/json' action="test/test" method="post" class="">
				<dl class="zend_form">

					<dt id="shifts-label">
						<label for="shifts" class="optional"><?=$this->translate('Shifts:')?></label>
					</dt>
					<dd id="shifts-element">
						<select name="shifts" id="new_shifts">
							<option value=""><?=$this->translate('Select shifts')?></option>
							<option value="Morning"><?=$this->translate('Morning')?></option>
							<option value="Evening"><?=$this->translate('Evening')?></option>
							<option value="Night"><?=$this->translate('Night')?></option>
						</select>
					</dd>
					
					<dt id="location-label">
						<label for="location"><?=$this->translate('Location:')?></label>
					</dt>
					<dd id="location-element">
						<select name="location" id="new_location">
							<option value=""><?=$this->translate('Select Location')?></option>
							<option value="22@"><?=$this->translate('22@')?></option>
							<option value="Avila"><?=$this->translate('Avila')?></option>
						</select>
					</dd>					
					
					<dt id="time-label">
						<label for="time_start" class="optional"><?=$this->translate('Start/End Time:')?></label>
					</dt>
					<dd id="time-element">
						<input type="text" name="time_start" style="width: 47%;" id="new_time_start" value=""><input type="text" name="time_end" style="width: 47%; float:right; clear:both;" id="new_time_end" value="">
					</dd>
					
					<dt id="group-label">
						<label for="group" class="optional"><?=$this->translate('Group Responsible:')?></label>
					</dt>
					<dd id="group-element">
						<select name="group" id="new_group">
							<option value=""><?=$this->translate('Select Group Responsible')?></option>
							<option value="Gene"><?=$this->translate('Gene')?></option>
							<option value="Internacional"><?=$this->translate('International')?></option>
							<option value="MMRR"><?=$this->translate('MMRR')?></option>
							<option value="Multi"><?=$this->translate('Multi')?></option>
							<option value="RdT"><?=$this->translate('RdT')?></option>
						</select>
					</dd>
					
					<dt id="client-label">
						<label for="client" class="optional"><?=$this->translate('Affected Client:')?></label>
					</dt>
					<dd id="client-element">
						<select name="client" id="new_client">
							<option value=""><?=$this->translate('Select Affected Client')?></option>
							<option value="CTT"><?=$this->translate('CTT')?></option>
							<option value="TSY"><?=$this->translate('TSY')?></option>
							<option value="EUL"><?=$this->translate('EUL')?></option>
							<option value="VWG"><?=$this->translate('VWG')?></option>
							<option value="MIC"><?=$this->translate('MIC')?></option>
						</select>
					</dd>		
					
				<!-- 	<dt id="status-label">
						<label for="status" class="optional">Status:</label>
					</dt>
				 	<dd id="status-element">
						<select name="status" id="new_status">
							<option value=""><?=$this->translate('Select Status')?></option>
							<option value="CTT"><?=$this->translate('CTT')?></option>
							<option value="TSY"><?=$this->translate('TSY')?></option>
							<option value="EUL"><?=$this->translate('EUL')?></option>
							<option value="VWG"><?=$this->translate('VWG')?></option>
							<option value="MIC"><?=$this->translate('MIC')?></option>
						</select>
					</dd>
					
					<dt id="incident-label">
					<label for="incident" class="optional">Incident/Change Number:</label>
					</dt>
					<dd id="incident-element">
						<input type="text" name="incident" id="new_incident" value="">
					</dd>-->
					
					<dt id="environment-label">
						<label for="environment" class="optional">Environment:</label>
					</dt>
					<dd id="environment-element">
						<select name="environment" id="new_environment">
							<option value=""><?=$this->translate('Select Environment')?></option>
							<option value="PRO"><?=$this->translate('PRO')?></option>
							<option value="PRE"><?=$this->translate('PRE')?></option>
							<option value="DES"><?=$this->translate('DES')?></option>
							<option value="INTEG"><?=$this->translate('INTEG')?></option>
						</select>
					</dd>

					<dt id="source-label">
						<label for="source" class="optional">Source:</label>
					</dt>
					<dd id="source-element">
						<select name="source" id="new_source">
							<option value="Checklist"><?=$this->translate('Checklist')?></option>
							<option value="Follow-up"><?=$this->translate('Follow-up')?></option>
						</select>
					</dd>
					
					<dt id="type-label">
						<label for="type" class="optional">Type:</label>
					</dt>
					<dd id="type-element">
						<select name="type" id="new_type">
							<option value="0"><?=$this->translate('Manual')?></option>
							<option value="1"><?=$this->translate('Operator Portal')?></option>
							<option value="2"><?=$this->translate('HPOO')?></option>
							<option value="3"><?=$this->translate('Other')?></option>
						</select>
					</dd>
					
					<dt id="title-label">
						<label for="title" class="optional">Title:</label>
					</dt>
					<dd id="title-element">
						<input type="text" name="title" id="new_title" value="">
					</dd>
										
					<dt id="description-label">
						<label for="description" class="optional">Description:</label>
					</dt>
					<dd id="description-element">
						<textarea name="description" class="textareas" id="new_description" style="height: 15px;"></textarea>
					</dd>
					
			<!-- 		<dt id="operator-label">
						<label for="operator" class="optional">Operator:</label>
					</dt>
					<dd id="operator-element">
						<input type="text" name="operator" id="new_operator" value="">
					</dd> -->
					
					<dt id="remark-label">
						<label for="remark" class="optional">Remark:</label>
					</dt>
					<dd id="remark-element">
						<textarea name="remark" class="textareas" id="new_remark" style="height: 15px;"></textarea>
					</dd>
					
					<dt id="Guardar-label">&nbsp;</dt>
					<dd id="Guardar-element">
						<input type="button" style="width: 47%;<!--  display: none;-->" name="update" id="new_update" value="Update"><input type="button" style="width: 47%; float:right; clear:both;" name="save" id="new_save" value="<?=$this->translate('Save')?>" >
						<input type="hidden" name="template"  id="new_temp" value="Y">
						<input type="hidden" name="recur" value="true" id="new_recur">
						<input type="hidden" name="key_recur" value="" id="new_key_recur">
						<input type="hidden" name="value_recur" value="" id="new_value_recur">
					</dd>
					<?php $this->Dialogs()->createRecurringEvent();?>
				</dl>

			</form>
		</div>
	</div>
</div>



<script type="text/javascript">
$(function() {

    $('#new_update').button({
        icons: {
            primary: "ui-icon-locked"
        }}).click(function(){
	// Muestra el dialogo y crea un evento
		{
			$('#createRecurringEvent').dialog({
				autoOpen: true,
				width: 700,
				modal: true,
				position: 'middle'
			});
			//alert($("#new_repeats").val());
	        $("#new_ttt").css('display','none');
	        $("#new_start-time").val($("#new_time_start").val());
	        $("#new_end-time").val($("#new_time_end").val());
	        
		};

  	});

    $('#new_save').button({
        icons: {
            primary: "ui-icon-locked"
        }}).click(function(){
	// Muestra el dialogo y crea un evento
		{
			ii = "0";
			$("#new_template_form :input").each(function() {
				   if($(this).val() === "")
					{
						ii++;
				   }   
				   alert (ii);
				if(ii=="0")
				{
					$('#new_update').show();
				 }
				});
		};
  	});

	
	$("#new_time_start").timepicker({
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
	
	$("#new_time_end").timepicker({
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


    $(".textareas").focusin(function () {
    	           $(this).animate({           
    	            height: '90px'
    	            },
    	           "slow"
	   	        )
    	    });

    $(".textareas").focusout(function () {
        $(this).animate({           
         height: '15px'
         },
        "slow"
        )
 });
	
});			

</script>