<script>
var initTransaction = {$jsonTransaction};
</script>
<link href="{$WEB_ROOT}/modules/gateways/binance/assets/css/binance-payment-page.css" rel="stylesheet">
<script src="{$WEB_ROOT}/modules/gateways/binance/assets/js/binance-payment-page.js"></script>
<div class="binance-crypto-payment-container">
	<h2>Pay by {$payableCurrency}</h2>
	<form class="binance-crypto-payment-step" id="binance-crypto-payment-step1">
		<div class="qr-code">
			<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$walletAddress}">
		</div>
		<div class="instructions">
			<p>
				Please send
				<span class="ltr">{$payableAmount} {$payableCurrency}</span> 
				to the following wallet address in the
				<span class="label label-success ltr">{$walletNetwork}</span> 
				network. Network's fee is not included.
			</p>
			<p>
				The final amount including the network fee is : 
				<span class="ltr">{$payableAmount+1} {$payableCurrency}</span>
			</p>
			{if $gatewayDiscount}
			<p>
				<span class="ltr label label-info">{$gatewayDiscount}%</span> 
				discount has been calculated.
			</p>
			{/if}
			<div class="input-group">
				<span class="input-group-btn">
					<button class="btn btn-default btn-copy" data-target="#wallet-address" type="button"><i class="far fa-copy"></i></button>
				</span>
				<input type="text" class="form-control ltr" id="wallet-address" readonly value="{$walletAddress}">
			</div>
			
		
			<p class="alert alert-danger">
				The address is ONLY available for
				<span class="ltr">{$payableCurrency}-{$walletNetwork}</span> 
				deposit. Deposit of other assets will lead to permanent asset loss.
			</p>
		
			<p class="text-center">
				<button type="submit" class="btn btn-default">
					<i class="fas fa-arrow-circle-right"></i> 
					Next 
				</button>
			</p>
		
		</div>
	</form>
	<form class="binance-crypto-payment-step" id="binance-crypto-payment-step2">
		<div class="qr-code">
			<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$walletAddress}">
		</div>
		<div class="instructions">
			<p>
				Please enter Transaction ID (TxID).
			</p>
			<div class="form-group">
				<input type="text" class="form-control ltr" name="tx_id" value="">
			</div>
			<p class="text-center">
				<button type="button" class="btn btn-default btn-back">
					<i class="fas fa-arrow-circle-left"></i> 
					Previous 
				</button>
				<button type="submit" class="btn btn-success">
					<i class="fas fa-arrow-circle-right"></i> 
					Next 
				</button>
			</p>
		</div>
	</form>
	<form class="binance-crypto-payment-step" id="binance-crypto-payment-step3">
		<div class="icon-spinner text-info">
			<i class="fas fa-spinner fa-spin"></i>
		</div>
		<h3 class="text-center">Wait ..</h3>
		<p>
			Your transaction has been completed, but we are waiting to receive confirmation from the network. 
			Usually, this step will take less than a few minutes and no action is required on your part.
		</p>

		<p class="text-info">
			<i class="fas fa-exclamation-circle"></i> 
			You can leave this page, the transaction is successfully saved in the system and your bill will be paid as soon as it is confirmed by the network.
		</p>

		<table class="table table-transaction">
			<tr>
				<th>ID:</th>
				<td class="ltr transaction-tx"></td>
			</tr>
			<tr>
				<th>Amount:</th>
				<td class="ltr transaction-amount-currency"></td>
			</tr>
			{if $gatewayDiscount}
			<tr>
				<th>Discount:</th>
				<td class="ltr"><span class="ltr label label-info">{$gatewayDiscount}%</span></td>
			</tr>
			{/if}
			<tr>
				<th>Date:</th>
				<td class="ltr transaction-submit-at"></td>
			</tr>
			<tr>
				<th>Confirmations:</th>
				<td><span class="transaction-confrimations"></span></td>
			</tr>
			<tr>
				<th>Status:</th>
				<td class="transaction-status"></td>
			</tr>
		</table>
	</form>
	<form class="binance-crypto-payment-step" id="binance-crypto-payment-step4">
		<div class="icon-success text-success">
			<i class="fas fa-check-square"></i>
		</div>
		<h3 class="text-center">Successfull!</h3>
		<p>
			Your payment has been confirmed in the amount of <span class="ltr transaction-amount-currency"></span>. You can now return to your invoice.
		</p>
		<table class="table table-transaction">
			<tr>
				<th>ID:</th>
				<td class="ltr transaction-tx"></td>
			</tr>
			<tr>
				<th>Amount:</th>
				<td class="ltr transaction-amount-currency"></td>
			</tr>
			{if $gatewayDiscount}
			<tr>
				<th>Discount:</th>
				<td class="ltr"><span class="ltr label label-info">{$gatewayDiscount}%</span></td>
			</tr>
			{/if}
			<tr>
				<th>Date:</th>
				<td class="ltr transaction-submit-at"></td>
			</tr>
			<tr>
				<th>Confirmations:</th>
				<td><span class="transaction-confrimations"></span></td>
			</tr>
			<tr>
				<th>Status:</th>
				<td class="transaction-status"></td>
			</tr>
		</table>
		<p class="text-center">
			<a type="submit" class="btn btn-default" href="{$invoice->getViewInvoiceUrl()}">
				<i class="fas fa-arrow-circle-right"></i> 
				View Transaction
			</a>
		</p>
	</form>
</div>

<div class="modal system-modal fade" id="modalGrowl" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel ">
			<div class="modal-header panel-heading">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Title</h4>
			</div>
			<div class="modal-body panel-body"></div>
			<div class="modal-footer panel-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>