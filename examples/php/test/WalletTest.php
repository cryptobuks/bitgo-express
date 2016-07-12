<?php

class WalletTest extends PHPUnit_Framework_TestCase {

	private static $testWallet;

	private static function getTestWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$memoizedWallet = self::$testWallet;
		if ($memoizedWallet instanceof \BitGo\Wallet) {
			return $memoizedWallet;
		}
		self::$testWallet = $bitgo->wallets()->getWallet('2MtepahRn4qTihhTvUuGTYUyUBkQZzaVBG3');
		return self::$testWallet;
	}

	public function testCreateAddress() {
		$testWallet = self::getTestWallet();
		$address = $testWallet->createAddress();
		$this->assertArrayHasKey('path', $address);
		$this->assertArrayHasKey('redeemScript', $address);
		$this->assertArrayHasKey('address', $address);
	}

	public function testListAddresses() {
		$testWallet = self::getTestWallet();
		$addresses = $testWallet->listAddresses();
		$this->assertArrayHasKey('addresses', $addresses);
		$this->assertArrayHasKey('start', $addresses);
		$this->assertArrayHasKey('count', $addresses);
		$this->assertArrayHasKey('total', $addresses);
		$firstAddress = $addresses['addresses'][0];
		$this->assertArrayHasKey('chain', $firstAddress);
		$this->assertArrayHasKey('index', $firstAddress);
		$this->assertArrayHasKey('path', $firstAddress);
	}

	public function testGetAddress() {
		$testWallet = self::getTestWallet();
		$address = $testWallet->getAddress($testWallet->getID());
		$this->assertEquals($testWallet->getID(), $address['address']);
		$this->assertEquals(0, $address['chain']);
		$this->assertEquals(0, $address['index']);
		$this->assertNotEmpty($address['redeemScript']);
	}

	public function testAddressLabels() {
		$testWallet = self::getTestWallet();
		$labelAddress = '2MxKo9RHNZHoPwmvnb5k8ytDJ6Shd1DHnsV';
		$testWallet->setAddressLabel($labelAddress, 'testLabel');
		$labels = $testWallet->listAddressLabels();
		$this->assertCount(1, $labels['labels']);
		$this->assertEquals('testLabel', $labels['labels'][0]['label']);
		$this->assertEquals($testWallet->getID(), $labels['labels'][0]['walletId']);
		$this->assertEquals($labelAddress, $labels['labels'][0]['address']);
		$testWallet->deleteAddressLabel($labelAddress);
	}

	public function testListUnspents() {
		$testWallet = self::getTestWallet();
		$unspents = $testWallet->listUnspents();
		$this->assertArrayHasKey('unspents', $unspents);
		$this->assertArrayHasKey('pendingTransactions', $unspents);
		$this->assertArrayHasKey('count', $unspents);
		$this->assertArrayHasKey('total', $unspents);
		$this->assertArrayHasKey('start', $unspents);
		$this->assertGreaterThanOrEqual($unspents['count'], $unspents['total']);
		$this->assertEquals($unspents['count'], count($unspents['unspents']));
		$firstUnspent = $unspents['unspents'][0];
		$this->assertEquals($testWallet->getID(), $firstUnspent['wallet']);
		$this->assertNotEmpty($firstUnspent['tx_hash']);
		$this->assertNotEmpty($firstUnspent['address']);
		$this->assertNotEmpty($firstUnspent['script']);
		$this->assertNotEmpty($firstUnspent['value']);
		$this->assertNotEmpty($firstUnspent['blockHeight']);
		$this->assertNotEmpty($firstUnspent['redeemScript']);
		$this->assertNotEmpty($firstUnspent['chainPath']);
		$this->assertNotEmpty($firstUnspent['confirmations']);
		$this->assertArrayHasKey('isChange', $firstUnspent);
		$this->assertArrayHasKey('fromSameWallet', $firstUnspent);
		$this->assertArrayHasKey('instant', $firstUnspent);
	}

	public function testListTransactions() {
		$testWallet = self::getTestWallet();
		$transactions = $testWallet->listTransactions();
		$this->assertArrayHasKey('transactions', $transactions);
		$this->assertArrayHasKey('start', $transactions);
		$this->assertArrayHasKey('count', $transactions);
		$this->assertArrayHasKey('total', $transactions);
		$this->assertGreaterThanOrEqual($transactions['count'], $transactions['total']);
		$this->assertEquals($transactions['count'], count($transactions['transactions']));
		$firstTx = $transactions['transactions'][0];
		$this->assertNotEmpty($firstTx['id']);
		$this->assertNotEmpty($firstTx['normalizedHash']);
		$this->assertNotEmpty($firstTx['fee']);

		// confirmations could be 0
		$this->assertNotNull($firstTx['confirmations']);
		// if confirmations < 1, blockhash is undefined
		if ($firstTx['confirmations'] > 0) {
			$this->assertNotEmpty($firstTx['blockhash']);
			$this->assertNotEmpty($firstTx['height']);
		}

		$this->assertNotEmpty($firstTx['inputs']);
		$this->assertNotEmpty($firstTx['outputs']);
		$this->assertNotEmpty($firstTx['entries']);
		$this->assertArrayHasKey('pending', $firstTx);
		$this->assertArrayHasKey('instant', $firstTx);
		$firstInput = $firstTx['inputs'][0];
		$firstOutput = $firstTx['outputs'][0];
		$firstEntry = $firstTx['entries'][0];
		$this->assertNotEmpty($firstInput['previousHash']);
		$this->assertNotNull($firstInput['previousOutputIndex']);
		$this->assertNotNull($firstOutput['vout']);
		$this->assertNotEmpty($firstOutput['account']);
		$this->assertNotEmpty($firstOutput['value']);
		$this->assertNotEmpty($firstEntry['account']);
		$this->assertNotEmpty($firstEntry['value']);
	}

	public function testWebhooks() {
		$testWallet = self::getTestWallet();

		$webhooks = $testWallet->listWebhooks();
		$webhookCount = count($webhooks['webhooks']);

		$webhook = $testWallet->createWebhook('transaction', 'https://fakesub.bitgo.com');
		$this->assertNotEmpty($webhook['id']);
		$this->assertNotEmpty($webhook['created']);
		$this->assertEquals($testWallet->getID(), $webhook['walletId']);
		$this->assertEquals('bitcoin', $webhook['coin']);
		$this->assertEquals('transaction', $webhook['type']);
		$this->assertEquals('https://fakesub.bitgo.com', $webhook['url']);

		$webhooks = $testWallet->listWebhooks();
		$this->assertCount($webhookCount + 1, $webhooks['webhooks']);

		$testWallet->deleteWebhook('transaction', 'https://fakesub.bitgo.com');

		$webhooks = $testWallet->listWebhooks();
		$this->assertCount($webhookCount, $webhooks['webhooks']);
	}

	public function testStats() {
		$testWallet = self::getTestWallet();
		$stats = $testWallet->getStats();
		$this->assertNotEmpty($stats['limit']);
		$this->assertNotEmpty($stats['nSends']);
		$this->assertNotEmpty($stats['nReceives']);
		$this->assertCount(9, $stats['sendSizes']);
		$this->assertCount(9, $stats['recvSizes']);
	}

	public function testTransactions() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallet = $bitgo->wallets()->getWallet('2MtepahRn4qTihhTvUuGTYUyUBkQZzaVBG3');
		$receiveAddress = $wallet->createAddress()['address'];
		$password = 'test wallet #1 security';

		try {
			$wallet->sendCoins($receiveAddress, 100000, $password, 'test tx');
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(401, $e->getCode());
			$this->assertEquals('needs unlock', $e->getMessage());
		}

		$bitgo->unlock('0000000');
		$transaction = $wallet->sendCoins($receiveAddress, 100000, $password, 'test tx');
		$bitgo->lock();

		$this->assertEquals('accepted', $transaction['status']);
		$this->assertGreaterThan(0, $transaction['fee']);
		$this->assertGreaterThan(0, $transaction['feeRate']);
		$this->assertNotNull($transaction['tx']);
		$this->assertNotNull($transaction['hash']);

		$transactionID = $transaction['hash'];

		// let the transaction be broadcast
		sleep(5); // wait for 5 seconds

		$refetchedTransaction = $wallet->getTransaction($transactionID);

		$this->assertEquals($transactionID, $refetchedTransaction['id']);
		$this->assertEquals($transaction['fee'], $refetchedTransaction['fee']);
		$this->assertEquals($transaction['tx'], $refetchedTransaction['hex']);
		$this->assertEquals('test tx', $refetchedTransaction['comment']);

		$this->assertNotEmpty($refetchedTransaction['normalizedHash']);
		$this->assertNotEmpty($refetchedTransaction['date']);
		$this->assertArrayHasKey('inputs', $refetchedTransaction);
		$this->assertArrayHasKey('outputs', $refetchedTransaction);
		$this->assertArrayHasKey('entries', $refetchedTransaction);
	}

	public function testStaticProperties() {
		$testWallet = self::getTestWallet();
		$this->assertGreaterThan(0, $testWallet->getBalance());
		$this->assertGreaterThan(0, $testWallet->getSpendableBalance());
		$this->assertGreaterThan(0, $testWallet->getConfirmedBalance());

		$this->assertGreaterThanOrEqual(0, $testWallet->getUnconfirmedSends());
		$this->assertGreaterThanOrEqual(0, $testWallet->getUnconfirmedReceives());

		$this->assertEquals('safehd', $testWallet->getType());
		$this->assertEquals(false, $testWallet->canSendInstant());
		$this->assertEquals(0, $testWallet->getInstantBalance());
	}
}
