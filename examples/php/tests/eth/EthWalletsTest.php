<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 7/6/16
 * Time: 2:48 PM
 */

namespace eth;

use BitGo\BitGoSDK;
use TestUtils;

class EthWalletsTest extends \PHPUnit_Framework_TestCase {

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
	}

	public function testGetWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$walletID = '0x9c532f9a429661e9199d447d63e4f182c16fb593';
		$wallet = $bitgo->eth()->wallets()->getWallet($walletID);
		$tx = $wallet->listTransfers();
	}
}
