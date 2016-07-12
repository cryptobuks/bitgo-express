<?php

namespace eth;

use BitGo\eth\EthWallet;
use Exception;
use TestUtils;

class EthWalletsTest extends \PHPUnit_Framework_TestCase {

	public function testListWallets() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallets = $bitgo->eth()->wallets()->listWallets();
		$this->assertArrayHasKey('limit', $wallets);
		$this->assertArrayHasKey('start', $wallets);
		$this->assertArrayHasKey('total', $wallets);
		$this->assertArrayHasKey('wallets', $wallets);
		$this->assertArrayHasKey('count', $wallets);
		$this->assertArrayHasKey('nextBatchPrevId', $wallets);
		$firstWallet = $wallets['wallets'][0];
		$this->assertEquals('eth', $firstWallet['type']);
		$this->assertNotEmpty($firstWallet['id']);
		$this->assertNotEmpty($firstWallet['label']);
		$this->assertArrayHasKey('permissions', $firstWallet);
	}

	public function testGenerateWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallet = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase');
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayHasKey('backupKeychain', $wallet);
		$this->assertEquals('Be sure to back up the backup keychain -- it is not stored anywhere else!', $wallet['warning']);
	}

	public function testGenerateWalletWithKRS() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallet = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase', 'keyternal');
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayHasKey('backupKeychain', $wallet);
		$ethWallet = $wallet['wallet'];
		$this->assertTrue($ethWallet instanceof EthWallet);
		$this->assertCount(3, $ethWallet->getSigningAddresses());
	}

	public function testGenerateWalletWithBackupXpub() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$backupXpub = 'xpub661MyMwAqRbcGU7FnXMKSHMwbWxARxYJUpKD1CoMJP6vonLT9bZZaWYq7A7tKPXmDFFXTKigT7VHMnbtEnjCmxQ1E93ZJe6HDKwxWD28M6f';
		$wallet = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase', null, null, $backupXpub);
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayHasKey('backupKeychain', $wallet);
		$this->assertEquals($backupXpub, $wallet['backupKeychain']['xpub']);
		$wallet['wallet']->delete();
	}

	public function testGenerateWalletWithBackupAddress() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$backupAddress = '0xfb32740232ecf3fd6d5a7bfc514a2cfb8a310e9b';
		$wallet = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase', null, $backupAddress);
		$this->assertArrayHasKey('wallet', $wallet);
		$this->assertArrayHasKey('userKeychain', $wallet);
		$this->assertArrayNotHasKey('backupKeychain', $wallet);
		$signingAddresses = $wallet['wallet']->getSigningAddresses();
		$this->assertCount(3, $signingAddresses);
		$this->assertEquals($backupAddress, $signingAddresses[1]['address']);
		$wallet['wallet']->delete();
	}

	public function testGetWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$walletID = '0x9c532f9a429661e9199d447d63e4f182c16fb593';
		$wallet = $bitgo->eth()->wallets()->getWallet($walletID);
		$tx = $wallet->listTransfers();
	}

	public function testWalletRename() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase', 'keyternal');
		$wallet = $response['wallet'];
		$oldName = $wallet->getName();
		$randomName = hash('sha256', rand(-10000, 10000));
		$wallet->setName($randomName);
		$refreshedWallet = $bitgo->eth()->wallets()->getWallet($wallet->getID());
		$newName = $refreshedWallet->getName();
		$this->assertEquals('Test Wallet', $oldName);
		$this->assertEquals($randomName, $newName);
		$refreshedWallet->delete();
	}

	public function testFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$bitgo->unlock('0000000');
		$wallet->freeze();
		$bitgo->lock();
		$wallet->delete();
	}

	public function testNegativeDurationFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase');
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
		$response = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase');
		$wallet = $response['wallet'];
		$bitgo->unlock('0000000');
		$lockDuration = 50;
		$wallet->freeze($lockDuration);
		$bitgo->lock();
		$refreshedWallet = $bitgo->eth()->wallets()->getWallet($wallet->getID());
		$this->assertNotEmpty($refreshedWallet->getRawWallet()['freeze']['time']);
		$this->assertNotEmpty($refreshedWallet->getRawWallet()['freeze']['expires']);
		$wallet->delete();
	}

	public function testLockedFreezeWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$response = $bitgo->eth()->wallets()->generateWallet('Test Wallet', 'test passphrase');
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

}
