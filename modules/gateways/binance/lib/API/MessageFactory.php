<?php
namespace WHMCS\Module\Gateway\Binance\API;

use GuzzleHttp\Message\MessageFactory as ParentMessageFactory;

class MessageFactory extends ParentMessageFactory {

	/**
	 * @var RequestSigner
	 */
	protected $requestSigner;

	public function __construct(RequestSigner $requestSigner, array $customOptions = [])
    {
        $this->requestSigner = $requestSigner;
		parent::__construct($customOptions);
    }

	public function createRequest($method, $url, array $options = [])
    {
        $request = parent::createRequest($method, $url, $options);
		$request = call_user_func($this->requestSigner, $request);
        return $request;
    }

}