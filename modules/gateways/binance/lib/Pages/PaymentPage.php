<?php
namespace WHMCS\Module\Gateway\Binance\Pages;

use Lang;

class PaymentPage extends InvoicePage {

	public function init() {
		parent::init();
		$this->setPageTitle('Pay by ' . $this->getGatewayCurrency()->code);
		$this->setupBreadCrumb();

		$payableCurrency = $this->getGatewayCurrency();
		$transaction = (new GetTransactionPage($this->invoiceID, null))->getTransaction();
		$transactionJson = $transaction ? $transaction->forAPI($this->getAPI()) : null;

		$this->assign('invoice', $this->getInvoice());
		$this->assign('payableAmount', $this->getPayableAmount($payableCurrency));
		$this->assign('payableCurrency', $payableCurrency->code);
		$this->assign('walletNetwork', $this->getGateway()->getParam("network"));
		$this->assign('walletAddress', $this->getWalletAddress());
		$this->assign('jsonTransaction', json_encode($transactionJson));
		$this->assign('gatewayDiscount', $this->getGatewayDiscount());
		$this->setTemplate('binance-crypto-payment');
	}

	public function setupBreadCrumb() {
		$this->addToBreadCrumb('index.php', Lang::trans('globalsystemname'));
		$this->addToBreadCrumb('viewinvoice.php?id=' . $this->invoiceID, 'Transaction #' . $this->invoiceID);
		$this->addToBreadCrumb('binance-crypto-payment.php?invoice=' . $this->invoiceID, 'Pay with crypto currency');
	}
}
