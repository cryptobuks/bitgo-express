<?php

include_once __DIR__ . '/vendor/autoload.php';

$sdk = new \BitGo\BitGoSDK();
print_r($sdk->authenticate('janedoe@bitgo.com', 'mypassword', '0000000'));

print_r($sdk->wallets()->listWallets());
print_r($sdk->unlock('0000000'));
$wallet = $sdk->wallets()->getWallet('mywalletid');
print_r($wallet->sendCoins('2MzmwGZFZQYNVcCJt7VDtcVreAmikeyscuY', 50000, 'mywalletpassword'));