<?php
namespace WHMCS\Module\Gateway\Binance;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Utils;
use GuzzleHttp\Middleware;
use GuzzleHttp\Message\RequestInterface;

/**
 * @phpstan-type DepositStatuses 'PENDING'|'CREDITED_BUT_CANNOT_WITHDRAW'|'WRONG_DEPOSIT'|'WAITING_USER_CONFIRM'|'SUCCESS'
 * 
 * @phpstan-type Deposit array{id:string,amount:string,coin:string,network:string,status:0|1|6|7|8,address: "bnb136ns6lfw4zs5hg4n85vdthaad7hq5m4gtkgf23",addressTag: "101764890",txId:string,insertTime:float,transferType:float,confirmTimes:string,unlockConfirm:int,walletType:int}
 */
class API
{
	const DEPOSIST_STATUSES = [
		'PENDING' => 0,
		'CREDITED_BUT_CANNOT_WITHDRAW' => 6,
		'WRONG_DEPOSIT' => 7,
		'WAITING_USER_CONFIRM' => 8,
		'SUCCESS' => 1,
	];

	protected $guzzle;

	public function __construct(string $apiKey, string $secretKey)
	{
		$signer = new API\RequestSigner($secretKey);

		$domain = 'https://api.binance.com/';

		$options = array(
			'base_url' => $domain, // For guzzle 5.3
			'base_uri' => $domain, // For guzzle 7.0
			'headers' => array(
				'User-Agent'   => 'binance-connect-php',
				'Content-Type' => 'application/json',
				'X-MBX-APIKEY' => $apiKey,
			),
			'defaults' => array(
				'headers' => array(
					'User-Agent'   => 'binance-connect-php',
					'Content-Type' => 'application/json',
					'X-MBX-APIKEY' => $apiKey,
				)
			),
		);

		if (class_exists(HandlerStack::class)) {
			$stack = new HandlerStack();
			$stack->setHandler(Utils::chooseHandler());
			$stack->push(Middleware::mapRequest($signer));
			$options['handler'] = $stack;
		} else {
			$options['message_factory'] = new API\MessageFactory($signer);
		}

		$this->guzzle = new Client($options);
	}

	/**
	 * @param array{coin:string,network?:string,amount?:string,recvWindow?:float} $data
	 * 
	 * @return array{address:string,coin:string,tag:string,url:string}
	 */
	public function getDepositAddress(array $data): array
	{
		$query = array_filter($data);

		$query['timestamp'] = time() * 1000;

		return $this->handleResponse($this->guzzle->get(
			"/sapi/v1/capital/deposit/address",
			['query' => $query]
		));
	}

	/**
	 * @param array{coin?:string,limit?:int,status?:DepositStatuses,startTime?:float,endTime?:float,offset?:int<0,max>,limit?:int<0,1000>,recvWindow?:float,txId?:string} $data
	 * 
	 * @return Deposit[]
	 */
	public function getDepositList(array $data = []): array
	{
		$query = array_filter($data);

		$query['timestamp'] = time() * 1000;

		return $this->handleResponse($this->guzzle->get(
			"/sapi/v1/capital/deposit/hisrec",
			['query' => $query]
		));
	}

	/**
	 * @return array<string,mixed>
	 */
	public function handleResponse($response): array
	{
		$body = Utils::jsonDecode((string) $response->getBody(), true);

		if (isset($body['message']) and 'success' == $body['message']) {
			return $body['data'];
		}

		return $body;
	}
}
