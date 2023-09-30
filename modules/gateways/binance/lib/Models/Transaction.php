<?php
namespace WHMCS\Module\Gateway\Binance\Models;

use Exception;
use WHMCS\Billing\Invoice;
use WHMCS\Model\AbstractModel;
use Carbon\Carbon;
use WHMCS\Module\Gateway\Binance\API;
use WHMCS\Module\Gateway\Binance\Exceptions\DepositNotFoundException;

class Transaction extends AbstractModel {

	const STATUS_PROCESSING = 1;
	const STATUS_CONFRIMING = 2;
	const STATUS_CANCEL = 3;
	const STATUS_FINISH = 4;

	public static function findDeposit(API $api, string $txID): ?array
	{
		$response = $api->getDepositList(['txId' => $txID]);
		return $response[0] ?? null;
	}

	protected $table = 'binance_transactions';
	public $timestamps = false;
	protected $casts = array(
		'submit_at' => 'datetime',
		'approve_at' => 'datetime',
	);
	/**
	 * @var array|null
	 */
	protected $deposit;

	public function invoice() {
		return $this->belongsTo(Invoice::class);
	}

	public function refreshFromAPI(API $api, bool $force = true)
	{
		if ($force) {
			$this->deposit = null;
		}

		$deposit = $this->getDeposit($api);

		$this->deposit_id = $deposit['id'];
		$this->amount = $deposit['amount'];
		$this->coin = $deposit['coin'];
		$this->confirmations = $deposit['confirmTimes'];

		switch ($deposit['status'])
		{
			case API::DEPOSIST_STATUSES['PENDING']:
				$this->status = self::STATUS_PROCESSING;
				break;
			case API::DEPOSIST_STATUSES['WAITING_USER_CONFIRM']:
				$this->status = self::STATUS_CONFRIMING;
				break;
			case API::DEPOSIST_STATUSES['WRONG_DEPOSIT']:
				$this->status = self::STATUS_CANCEL;
				break;
			case API::DEPOSIST_STATUSES['SUCCESS']:
			case API::DEPOSIST_STATUSES['CREDITED_BUT_CANNOT_WITHDRAW']:
				$this->status = self::STATUS_FINISH;
				break;
		}

		$this->save();
	}

	public function getDeposit(API $api): array
	{
		if (!$this->deposit) {
			$this->deposit = self::findDeposit($api, $this->tx_id, $this->coin);
		}

		if (!$this->deposit) {
			throw new DepositNotFoundException("Cannot find deposit");
		}

		return $this->deposit;
	}

	public function forAPI(API $api): array
	{
		$deposit = $this->getDeposit($api);
		$this->refreshFromAPI($api, false);
		
		return array(
			'id' => $this->id,
			'invoice_id' => $this->invoice_id,
			'tx_id' => $this->tx_id,
			'submit_at' => $this->submit_at->__toString(),
			'approve_at' => $this->approve_at ? $this->approve_at->__toString() : null,
			'amount' => $this->amount,
			'coin' => $this->coin,
			'confirmations' => $this->confirmations,
			'status' => $this->status,
		);
	}

	
}
