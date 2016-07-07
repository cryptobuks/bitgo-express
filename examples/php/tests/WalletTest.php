<?php

class WalletTest extends PHPUnit_Framework_TestCase {

	private static function getTestWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		return $bitgo->wallets()->getWallet('2NBQAdMHtWYJPxmNNw6CGyVqdsvERFjLLG4');
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
}
