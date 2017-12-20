<div class="cmo-backoffice">
	<form id="configuration_form" method="post" enctype="multipart/form-data" novalidate>
		<input type="hidden" name="submitpaymuna" value="1" />
		<fieldset>
			<legend>
				API CREDENTIALS
			</legend>
			<div class="form-group">
		    	<label for="checkout_token"><span style="color:red">*</span> {l s="API TOKEN" mod="paymuna"}</label>
		    	<input type="text"
					   name="checkout_token"
					   class="form-control"
					   id="api_token"
					   placeholder="API Token"
					   value="{$config['PAYMUNA_API_TOKEN']}">
		  	</div>
			<div class="form-group">
		    	<label for="checkout_secret"><span style="color:red">*</span> {l s="API SECRET" mod="paymuna"}</label>
		    	<input type="text"
					   name="checkout_secret"
					   class="form-control"
					   id="api_secret"
					   placeholder="API Secret"
					   value="{$config['PAYMUNA_API_SECRET']}">
		  	</div>
			<div class="form-group">
		    	<label for="checkout_session"><span style="color:red">*</span> {l s="REFERENCE ID" mod="paymuna"}</label>
		    	<input type="text"
					   name="checkout_session"
					   class="form-control"
					   id="reference_id"
					   placeholder="Checkout Template Reference ID"
					   value="{$config['PAYMUNA_REFERENCE']}">
		  	</div>
		  	<div class="form-group">
		    	<label for="checkout_session"><span style="color:red">*</span> {l s="ENVIRONMENT" mod="paymuna"}</label>
		    	<br>
				
				<label for="environment_test">
			    	<input type="radio" name="checkout_environment" id="environment_test" value="test" {if $config['PAYMUNA_ENVIRONMENT'] == 'test'}checked{/if} {if  !isset($config['PAYMUNA_ENVIRONMENT']) || !$config['PAYMUNA_ENVIRONMENT']}checked{/if}>
			    	Test
				</label>&nbsp;

				<label for="environment_live">
			    	<input type="radio" name="checkout_environment" id="environment_live" value="live" {if $config['PAYMUNA_ENVIRONMENT'] == 'live'}checked{/if}>
			    	Live
				</label>
		  	</div>
		</fieldset>
		<br />
		<div class="panel-footer">
			<button type="submit" value="1" id="configuration_form_submit_btn" name="submitcheckmeout" class="button">
				Save Changes
			</button>
		</div>
	</form>
</div>

<style type="text/css">
	.cmo-backoffice {
		background-color: #FFFFFF;
		padding: 35px;
		width: 100%;
	}

	.cmo-backoffice .panel-footer {
		background-color: transparent !important;
		padding-top: 20px;
		text-align: right;
	}

	.cmo-backoffice .panel-footer button {
		background: #ffbf00;
	    border-radius: 20px;
	    border: 2px solid #f2b605;
	    color: #73540c;
	    padding: 7px 35px;
	}
</style>
