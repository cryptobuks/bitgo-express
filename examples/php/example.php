<?php

include_once __DIR__ . '/BitGoSDK.php';

$sdk = new BitGoSDK();
print_r($sdk->login('janedoe@bitgo.com', 'mypassword', '0000000'));

print_r($sdk->listWallets());
print_r($sdk->unlock('0000000'));
print_r($sdk->sendCoins('mywalletid', 'mywalletpassword', '2MzmwGZFZQYNVcCJt7VDtcVreAmikeyscuY', 50000)); // send 50,000 satoshis