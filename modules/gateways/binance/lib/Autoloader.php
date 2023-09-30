<?php
namespace WHMCS\Module\Gateway\Binance;

use Composer\Autoload\ClassLoader;

class Autoloader extends ClassLoader {
	public static function init() {
		$me = new self();
		$me->register();
	}

	public function __construct() {
		$this->addPsr4(__NAMESPACE__ . "\\", [__DIR__]);
	}
}