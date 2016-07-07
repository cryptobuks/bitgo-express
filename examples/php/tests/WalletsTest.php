<?php

class WalletsTest extends PHPUnit_Framework_TestCase {

	public function testListWallets() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallets = $bitgo->wallets()->listWallets();
		$this->assertArrayHasKey('limit', $wallets);
		$this->assertArrayHasKey('start', $wallets);
		$this->assertArrayHasKey('total', $wallets);
		$this->assertArrayHasKey('count', $wallets);
		$this->assertArrayHasKey('nextBatchPrevId', $wallets);
		$this->assertArrayHasKey('wallets', $wallets);
		$this->assertCount($wallets['count'], $wallets['wallets']);
		$this->assertGreaterThanOrEqual(5, $wallets['count']);
		$this->assertGreaterThan($wallets['count'], $wallets['total']);
	}

	public function testCreateWalletWithKRS() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallet = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase', 'keyternal');
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayHasKey('backupKeychain', $wallet);
		$this->assertArrayHasKey('bitgoKeychain', $wallet);
	}
}
