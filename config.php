<div class="caldera-config-group">
	<label><?php echo __('Key Name', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config" name="{{_name}}[name]" value="{{name}}">
	</div>
</div>
<div class="caldera-config-group">
	<label><?php echo __('Pattern', 'cf-keygen'); ?> </label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config" name="{{_name}}[pattern]" value="{{#if pattern}}{{pattern}}{{else}}****-****-****-****{{/if}}">
	</div>
</div>
<p>* = Alpha-Numerical Character</p>
<p># = Numerical Character</p>
<p>& = Alphabet Character</p>
<p>Any other character is static.</p>