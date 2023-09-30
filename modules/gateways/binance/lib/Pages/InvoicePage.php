<?php
namespace WHMCS\Module\Gateway\Binance\Pages;

use \Exception;
use WHMCS\ClientArea;
use WHMCS\Billing\Currency;
use WHMCS\Billing\Invoice;
use WHMCS\Module\Gateway;
use WHMCS\Module\Gateway\Binance\API;

class InvoicePage extends ClientArea {
	
	protected $invoiceID;

	/**
	 * @var Gateway|null
	 */
	protected $gateway;

	/**
	 * @var Binance|null
	 */
	protected $api;

	/**
	 * @var Currency|null
	 */
	protected $gatewayCurrency;

	/**
	 * @var Invoice|null
	 */
	protected $invoice;


	/**
	 * @var Currency|null
	 */
	protected $invoiceCurrency;

	public function __construct(int $invoiceID) {
		$this->invoiceID = $invoiceID;
		parent::__construct();
	}

	public function init() {
		$this->initPage();
		$this->requireLogin();
	}

	public function getGateway(): Gateway {
		if (!$this->gateway) {
			$this->gateway = Gateway::factory("binance");
			$gatewayCurrencyID = $this->gateway->getParam("convertto");
			if (!$gatewayCurrencyID) {
				throw new Exception("You must set a currency for this gateway");
			}
			$gatewayNetwork = $this->gateway->getParam("network");
			if (!$gatewayNetwork) {
				throw new Exception("You must set a blockchain network for this gateway");
			}
			$gatewayAPIKey = $this->gateway->getParam("apiKey");
			if (!$gatewayAPIKey) {
				throw new Exception("You must set a blockchain api key for this gateway");
			}
			$gatewaySecretKey = $this->gateway->getParam("secretKey");
			if (!$gatewaySecretKey) {
				throw new Exception("You must set a blockchain secret key for this gateway");
			}
		}
		return $this->gateway;
	}

	public function getGatewayCurrency(): Currency {
		if (!$this->gatewayCurrency) {
			$this->gatewayCurrency = Currency::query()->findOrFail($this->getGateway()->getParam("convertto"));
		}
		return $this->gatewayCurrency;
	}

	public function getGatewaySlippageTolerance(): int {
		return intval($this->getGateway()->getParam("slippageTolerance"));
	}
	public function getGatewayDiscount(): int {
		return intval($this->getGateway()->getParam("discount"));
	}

	public function getAPI(): API {
		if (!$this->api) {
			$gateway = $this->getGateway();
			$this->api = new API($gateway->getParam("apiKey"), $gateway->getParam("secretKey"));		}
		return $this->api;
	}

	public function getWalletAddress(): string {
		$currency = $this->getGatewayCurrency()->code;
		$network = $this->getGateway()->getParam("network");
		return $this->getAPI()->getDepositAddress([
			'coin' => $currency,
			'network' => $network,
		])['address'];
	}

	public function getInvoice(): Invoice {
		if (!$this->invoice) {
			$this->invoice = Invoice::query()
				->where("userid", $_SESSION['uid'])
				->findOrFail($this->invoiceID);
		}
		return $this->invoice;
	}

	public function getInvoiceCurrency(): Currency {
		if (!$this->invoiceCurrency) {
			$this->invoiceCurrency = Currency::query()->findOrFail($this->getInvoice()->getCurrency()['id']);
		}
		return $this->invoiceCurrency;
	}

	public function getPayableAmount(Currency $currency): float {
		$invoice = $this->getInvoice();
		$discount = $this->getGatewayDiscount() / 100;
		$amount = $invoice->getBalanceAttribute();
		$amount *= 1 - $discount;
		return (new Invoice\Helper())
			->convertCurrency($amount, $currency, $invoice);
	}
}
