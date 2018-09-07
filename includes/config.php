<?php
/**
 * 2Checkout Processor setup template.
 * Settings template.
 */

if( ! is_ssl() ){
?>
	<div class="error" style="border-left-color: #FF0;">
		<p>
			<?php esc_html_e( 'Your site is not using secure HTTPS. SSL/HTTPS is not required to use PayFast for Caldera Forms, but it is recommended.', 'cf-payfast' ); ?>
		</p>
	</div>
<?php } ?>

<div class="caldera-config-group">
	<label for="{{_id}}_sandbox"><?php _e('Sandbox Mode', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">
		<input id="{{_id}}_sandbox" type="checkbox" class="field-config" name="{{_name}}[sandbox]" value="1" {{#if sandbox}}checked="checked"{{/if}}>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Merchant ID', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_merchant_id" class="block-input required field-config" name="{{_name}}[merchant_id]" value="{{merchant_id}}" required >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Merchant Key', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_merchant_key" class="block-input required field-config" name="{{_name}}[merchant_key]" value="{{merchant_key}}" required >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('User Email', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_email_address" class="block-input required field-config magic-tag-enabled" name="{{_name}}[email_address]" value="{{email_address}}" required >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('User First Name', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_name_first" class="block-input field-config magic-tag-enabled" name="{{_name}}[name_first]" value="{{name_first}}" >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('User Last Name', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_name_last" class="block-input field-config magic-tag-enabled" name="{{_name}}[name_last]" value="{{name_last}}" >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('User Cell No.', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_cell_number" class="block-input field-config magic-tag-enabled" name="{{_name}}[cell_number]" value="{{cell_number}}" >
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Initial Amount', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="initial_amount" type="calculation,text,hidden" exclude="system"}}}
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Item Name', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_item_name" class="block-input field-config magic-tag-enabled" name="{{_name}}[item_name]" value="{{item_name}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Item Description', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_item_description" class="block-input field-config magic-tag-enabled" name="{{_name}}[item_description]" value="{{item_description}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Payment Method', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_payment_method" class="field-config" name="{{_name}}[payment_method]">
			<option value=""><?php _e( 'All Payment Methods', 'cf-payfast' ); ?></option>
			<option value="eft" {{#is payment_method value="eft"}}selected="selected"{{/is}}><?php _e( 'EFT', 'cf-payfast' ); ?></option>
			<option value="cc" {{#is payment_method value="cc"}}selected="selected"{{/is}}>Credit Card</option>
			<option value="dc" {{#is payment_method value="dc"}}selected="selected"{{/is}}>Debit Card</option>
			<option value="bc" {{#is payment_method value="bc"}}selected="selected"{{/is}}>BitCoin Payment</option>
			<option value="mp" {{#is payment_method value="mp"}}selected="selected"{{/is}}>MasterPass</option>
			<option value="mc" {{#is payment_method value="mc"}}selected="selected"{{/is}}>MobiCred</option>
			<option value="cd" {{#is payment_method value="cd"}}selected="selected"{{/is}}>Cash Deposit</option>
		</select>
			<small>Set to specific payment method.</small>
	</div>

</div>


<div class="caldera-config-group">
	<label for="{{_id}}_recurring"><?php _e( 'Recurring', 'cf-payfast' ); ?></label>
	<div class="caldera-config-field">
		<input id="{{_id}}_recurring" type="checkbox" class="field-config" name="{{_name}}[recurring]" value="1" {{#if recurring}}checked="checked"{{/if}} >
	</div>
</div>

<!-- Only show these fields if recurring selected -->
<div class="caldera-config-group recurring-show">
	<label><?php _e( 'Recurring Amount', 'cf-payfast' ); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="recurring_amount" type="calculation,text,hidden" exclude="system"}}}
	</div>
</div>

<div class="caldera-config-group recurring-show">
	<label><?php _e('Frequency', 'cf-payfast'); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_frequency" class="field-config" name="{{_name}}[frequency]">
			<option value=3 {{#is frequency value=3}}selected="selected"{{/is}}>Monthly</option>
			<option value=4 {{#is frequency value=4}}selected="selected"{{/is}}>Quarterly</option>
			<option value=5 {{#is frequency value=5}}selected="selected"{{/is}}>Biannual</option>
			<option value=6 {{#is frequency value=6}}selected="selected"{{/is}}>Annual</option>
		</select>
	</div>
</div>

<div class="caldera-config-group recurring-show">
	<label><?php _e( 'Billing Cycles', 'cf-payfast' ); ?></label>
	<div class="caldera-config-field">		
		<input type="number" id="{{_id}}_billing_cycles" class="block-input field-config" name="{{_name}}[billing_cycles]" value="{{billing_cycles}}" min="0" max="999">
	</div>
	<small>The number of payments/cycles that will occur for this subscription. Set to 0 for infinity.</small>
</div>

<style type="text/css">
	.recurring-show{display:none;}
</style>

<script type="text/javascript">
	jQuery(document).ready(function(){
		var pID = "{{_id}}";

		var recurring = jQuery( '#' + pID + '_recurring' ).is(":checked");

		if( recurring === true ) {
			jQuery('.recurring-show').show();
		}

			jQuery( '#' + pID + '_recurring' ).click( function(){
				
					jQuery('.recurring-show').toggle();
			});
		
	});
	
</script>