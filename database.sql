CREATE TABLE `binance_transactions` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`invoice_id` int(11) NOT NULL,
	`tx_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	`submit_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`approve_at` timestamp NULL DEFAULT NULL,
	`amount` double DEFAULT NULL,
	`coin` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
	`confirmations` int(10) unsigned DEFAULT NULL,
	`deposit_id` bigint(20) unsigned DEFAULT NULL,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `transaction_id` (`tx_id`),
	KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
