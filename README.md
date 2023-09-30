# Compatibility
This gateway tested agianst WHMCS 7.10.2 and 8.1.3 with six template.

It's hard-coded in Persian language and RTL layout.

# HOWTO Install
1. Upload content of this repository to whmcs.
2. Import `database.sql` into the WHMCS database and it's better to remove it after that.
3. Go to admin-panel -> Setup -> Payment -> Currencies and make a crypto-currency with corresponding symbol. (e.g. USDT)
4. Go to [Binance API Management](https://www.binance.com/en/my/settings/api-management) page and make new API token with readonly access.
5. Go to admin-panel -> Setup -> Payment -> Payment Gateways and make `binance` active.
6. Enter your API access and select crypto-currency that you created in third setup.
7. Test it!
8. If it's all good, setup a cronjob to process pending transactions in background:
```
* * * * * php ~/public_html/binance-crypto-payment.php > /dev/null
```