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
		$wallet['wallet']->delete();
	}

	public function testCreateWalletWithBackupXpub() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$backupXpub = 'xpub661MyMwAqRbcGU7FnXMKSHMwbWxARxYJUpKD1CoMJP6vonLT9bZZaWYq7A7tKPXmDFFXTKigT7VHMnbtEnjCmxQ1E93ZJe6HDKwxWD28M6f';
		$wallet = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase', null, $backupXpub);
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayHasKey('backupKeychain', $wallet);
		$this->assertArrayHasKey('bitgoKeychain', $wallet);
		$this->assertEquals($backupXpub, $wallet['backupKeychain']['xpub']);
		$wallet['wallet']->delete();
	}

	public function testFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$bitgo->unlock('0000000');
		$wallet->freeze();
		$bitgo->lock();
		$wallet->delete();
	}

	public function testNegativeDurationFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$bitgo->unlock('0000000');
		try {
			$wallet->freeze(-50);
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(400, $e->getCode());
			$this->assertEquals('invalid limit', $e->getMessage());
		}
		$bitgo->lock();
		$wallet->delete();
	}

	public function testDurationFreezeWalletRetrieval() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$bitgo->unlock('0000000');
		$lockDuration = 50;
		$wallet->freeze($lockDuration);
		$bitgo->lock();
		$refreshedWallet = $bitgo->wallets()->getWallet($wallet->getID());
		$this->assertNotEmpty($refreshedWallet->getRawWallet()['freeze']['time']);
		$this->assertNotEmpty($refreshedWallet->getRawWallet()['freeze']['expires']);
		$wallet->delete();
	}

	public function testLockedFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		try {
			$wallet->freeze();
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(401, $e->getCode());
			$this->assertEquals('needs unlock', $e->getMessage());
		}
		$wallet->delete();
	}

	public function testWalletRename(){
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->wallets()->createWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$oldName = $wallet->getName();
		$randomName = hash('sha256', rand(-10000, 10000));
		$wallet->setName($randomName);
		$refreshedWallet = $bitgo->wallets()->getWallet($wallet->getID());
		$newName = $refreshedWallet->getName();
		$this->assertEquals('Test Wallet', $oldName);
		$this->assertEquals($randomName, $newName);
		$refreshedWallet->delete();
	}
}
