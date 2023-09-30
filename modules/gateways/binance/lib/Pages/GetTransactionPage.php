<?php
namespace WHMCS\Module\Gateway\Binance\Pages;

use Carbon\Carbon;
use WHMCS\Module\Gateway\Binance\Models\Transaction;
use WHMCS\Billing\Invoice;
use WHMCS\Billing\Currency;

class GetTransactionPage extends InvoicePage {
	/**
	 * @var string|null
	 */
	protected $txID;

	/**
	 * @var Transaction|null
	 */
	protected $transaction;

	public function __construct(int $invoiceID, ?string $txID) {
		parent::__construct($invoiceID);
		$this->txID = $txID;
	}

	public function output() {
		header("Content-Type: application/json");
		echo json_encode($this->getOutputData());
	}

	public function getOutputData()
	{
		$transaction = $this->getTransaction();

		if (!$transaction) {
			return array(
				'status' => true,
				'transaction' => null,
			);
		}

		if ($transaction->submit_at->diffInDays() > 2) {
			return array(
				'status' => false,
				'error' => 'transaction-too-old',
			);
		}

		$transaction->refreshFromAPI($this->getAPI());

		if ($transaction->status == Transaction::STATUS_FINISH and $transaction->approve_at === null) {
			$this->addPaymentToInvoice();
			$transaction->approve_at = Carbon::now();
		}

		return array(
			'status' => true,
			'transaction' => $transaction->forAPI($this->getAPI()),
		);
	}

	public function getTransaction(): ?Transaction {
		if (!$this->transaction) {
			if ($this->txID) {
				$this->transaction = Transaction::query()
					->where("tx_id", $this->txID)
					->where("invoice_id", $this->invoiceID)
					->first();
				
			} else {
				$query = Transaction::query();
				$query->where("invoice_id", $this->invoiceID);
				$query->orderBy("id", "DESC");
				$invoice = $this->getInvoice();
				if ($invoice->status === Invoice::STATUS_UNPAID) {
					$query->whereIn("status", [Transaction::STATUS_PROCESSING, Transaction::STATUS_CONFRIMING]);
				}
				$this->transaction = $query->first();
			}
		}
		return $this->transaction;
	}
	protected function addPaymentToInvoice() {
		$transaction = $this->getTransaction();
		$slippage = $this->getGatewaySlippageTolerance() / 100;
		$discount = $this->getGatewayDiscount() / 100;
		$invoiceCurrency = $this->getInvoiceCurrency();
		$paidCurrency = Currency::query()
			->where("code", $transaction->coin)
			->firstOrFail();

		$invoiceBalance = $this->getInvoice()->getBalanceAttribute();
		$paidAmountInInvoiceCurrency = $transaction->amount * $paidCurrency->rate / $invoiceCurrency->rate;
		$paidAmountInInvoiceCurrency *= 1 + $discount;
		if (abs(1 - ($paidAmountInInvoiceCurrency / $invoiceBalance)) < $slippage) {
			$paidAmountInInvoiceCurrency = $invoiceBalance;
		}

		checkCbTransID($transaction->tx_id);
		logTransaction("binance", array(
			"id" => $transaction->id,
			"invoice_id" => $transaction->invoice_id,
			"tx_id" => $transaction->tx_id,
			"submit_at" => $transaction->submit_at->__toString(),
			"approve_at" => $transaction->approve_at ? $transaction->approve_at->__toString() : null,
			"amount" => $transaction->amount,
			"coin" => $transaction->coin,
			"confirmations" => $transaction->confirmations,
		), "Finish");
		addInvoicePayment($transaction->invoice_id, $transaction->tx_id, $paidAmountInInvoiceCurrency, 0, "binance");
	}
}
