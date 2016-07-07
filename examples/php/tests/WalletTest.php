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

	public function testListLabels() {
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
		$this->assertNotEmpty($firstTx['confirmations']);
		$this->assertNotEmpty($firstTx['blockhash']);
		$this->assertNotEmpty($firstTx['height']);
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
}
