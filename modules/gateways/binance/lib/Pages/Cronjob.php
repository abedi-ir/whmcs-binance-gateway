<?php
namespace WHMCS\Module\Gateway\Binance\Pages;

use Carbon\Carbon;
use WHMCS\Module\Gateway\Binance\Models\Transaction;

class Cronjob extends GetTransactionPage {

	public function __construct() {}
	public function init() {}

	public function output() {
		echo json_encode($this->getOutputData(),  JSON_PRETTY_PRINT) . "\n";
	}

	public function getOutputData() {
		$pendingTransactions = Transaction::query()
			->whereIn("status", [Transaction::STATUS_PROCESSING, Transaction::STATUS_CONFRIMING])
			->get();
		$result = [];
		foreach ($pendingTransactions as $transaction) {
			try {
				$this->txID = $transaction->tx_id;
				$this->invoiceID = $transaction->invoice_id;
				$result[] = parent::getOutputData();
			} catch (\Throwable $e){}
		}
		return $result;
	}
}
