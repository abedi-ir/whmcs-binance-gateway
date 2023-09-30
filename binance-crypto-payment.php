<?php

use WHMCS\Module\Gateway\Binance\Autoloader;
use WHMCS\Module\Gateway\Binance\Pages\PaymentPage;
use WHMCS\Module\Gateway\Binance\Pages\GetTransactionPage;
use WHMCS\Module\Gateway\Binance\Pages\SubmitTransactionPage;
use WHMCS\Module\Gateway\Binance\Pages\Cronjob;

define('CLIENTAREA', true);
require __DIR__ . '/init.php';
Autoloader::init();

if (!isset($_SERVER['REMOTE_ADDR'])) {
	$page = new Cronjob();
} elseif (!isset($_GET['action'])) {
	$page = new PaymentPage($_GET['invoice'] ?? 0);
} elseif ($_GET['action'] == 'get-transaction') {
	$page = new GetTransactionPage($_GET['invoice'] ?? 0, $_GET['tx_id'] ?? null);
} elseif ($_GET['action'] == 'submit-transaction') {
	if (!isset($_POST['tx_id']) or !is_string($_POST['tx_id'])) {
		return;
	}
	$page = new SubmitTransactionPage($_GET['invoice'] ?? 0, $_POST['tx_id']);
}
$page->init();
$page->output();
