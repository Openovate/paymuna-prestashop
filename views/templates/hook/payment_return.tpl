{if $status == 'ok'}
<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='paymuna'}
		<br />
		<br /><br />- {l s='Reference #' mod='paymuna'}  <strong>{$objOrder->reference}</strong>
		<br /><br />- {l s='Payment Method' mod='paymuna'}  <strong>{$objOrder->payment}</strong>
		<br /><br />- {l s='Amount' mod='paymuna'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br />
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='paymuna'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='paymuna'}</a>.
	</p>
{/if}

<script type="text/javascript">
	$('.step').addClass('hide');
</script>
