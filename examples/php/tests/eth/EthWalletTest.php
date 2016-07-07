<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 7/7/16
 * Time: 11:52 AM
 */

namespace eth;


use TestUtils;

class EthWalletTest extends \PHPUnit_Framework_TestCase {
	private static $testWallet;

	private static function getTestWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$memoizedWallet = self::$testWallet;
		if ($memoizedWallet instanceof \BitGo\Wallet) {
			return $memoizedWallet;
		}
		self::$testWallet = $bitgo->eth()->wallets()->getWallet('0x9c532f9a429661e9199d447d63e4f182c16fb593');
		return self::$testWallet;
	}

	public function testCreateAddress() {
		$testWallet = self::getTestWallet();
		$address = $testWallet->createAddress();
		$this->assertEquals($testWallet->getID(), $address['walletAddress']);
		$this->assertNotEmpty($address['address']);
		$this->assertNotEmpty($address['walletNonce']);
		$this->assertNotEmpty($address['deployTxHash']);
	}

}
