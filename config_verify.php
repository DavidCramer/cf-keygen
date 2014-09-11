<?php

// finds registered forms that have the processor
$options = array();
$forms = get_option('_caldera_forms');
foreach($forms as $form_id=>$form_cfg){
	$set_processors = Caldera_Forms::get_processor_by_type('keygen', $form_id);
	if(!empty($set_processors)){
		foreach($set_processors as $set_processor){
			//print_r($set_processor);
			$options[] = '<option value="'.$set_processor['ID'].'" {{#is connect value="'.$set_processor['ID'].'"}}selected="selected"{{/is}}>'.$set_processor['config']['name'].'</option>';
		}
	}
}
//$forms = Caldera_Forms::get

?>
<div class="caldera-config-group">
	<label><?php echo __('Validate Against', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[connect]">
			<option value="">Any</option>
			<?php echo implode('', $options); ?>
		</select>
		<p>Connect to specific key</p>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php echo __('Key Input', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_key" class="block-input field-config magic-tag-enabled" name="{{_name}}[key]" value="{{key}}">
		<p>Input in which to verify a key</p>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php echo __('Verification Limit', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_limit" class="block-input field-config" name="{{_name}}[limit]" value="{{limit}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php echo __('Failed Banner', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_fail_banner" class="block-input field-config magic-tag-enabled" name="{{_name}}[fail_banner]" value="{{fail_banner}}">
	</div>
</div>
<?php
/*
<div class="caldera-config-group">
	<label><?php echo __('Failed caption', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_fail_field" class="block-input field-config magic-tag-enabled" name="{{_name}}[fail_field]" value="{{fail_field}}">
	</div>
</div>
*/
?>