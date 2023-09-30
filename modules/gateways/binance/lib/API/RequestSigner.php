<?php
namespace WHMCS\Module\Gateway\Binance\API;


use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use GuzzleHttp\Psr7\Uri;

class RequestSigner
{
	/**
	 * @var string
	 */
	protected $secretKey;

	public function __construct(string $secretKey) {
		$this->secretKey = $secretKey;
	}

	/**
	 * @var GuzzleRequestInterface|PsrRequestInterface
	 */
	public function __invoke($request)
	{
		$timestamp = time() * 1000;

		if ($request instanceof PsrRequestInterface) {
			parse_str($request->getUri()->getQuery(), $params);
		} else {
			$params = $request->getQuery()->toArray();
		}
		
		$params = http_build_query($params);

		$signature = hash_hmac('sha256', $params, $this->secretKey);

		if ($request instanceof PsrRequestInterface) {
			$uri = $request->getUri();
			$uri = Uri::withQueryValue($uri, 'signature', $signature);
			$request = $request->withUri($uri);
		} else {
			$query = $request->getQuery();
			$query->add('signature', $signature);
		}

		return $request;
	}
}
