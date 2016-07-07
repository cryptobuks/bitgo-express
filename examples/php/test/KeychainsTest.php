<?php

class KeychainsTest extends PHPUnit_Framework_TestCase {

	public function testListKeychains() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$keychains = $bitgo->keychains()->listKeychains();
		$this->assertArrayHasKey('keychains', $keychains);
		$this->assertArrayHasKey('total', $keychains);
		$this->assertArrayHasKey('start', $keychains);
		$this->assertArrayHasKey('count', $keychains);
		$this->assertArrayHasKey('limit', $keychains);
		$this->assertCount($keychains['count'], $keychains['keychains']);
		$this->assertGreaterThanOrEqual(5, $keychains['count']);
		$this->assertGreaterThan($keychains['count'], $keychains['total']);
	}

	public function testCreateKeychain() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$newKeychain = $bitgo->keychains()->createKeychain();
		$this->assertArrayHasKey('ethAddress', $newKeychain);
		$this->assertArrayHasKey('xpub', $newKeychain);
		$this->assertArrayHasKey('xprv', $newKeychain);
		$this->assertArrayNotHasKey('path', $newKeychain);
	}

	public function testCreateBitGoKeychain() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$bitgoKeychain = $bitgo->keychains()->createBitGoKeychain();
		$this->assertArrayHasKey('path', $bitgoKeychain);
		$this->assertArrayHasKey('ethAddress', $bitgoKeychain);
		$this->assertArrayHasKey('xpub', $bitgoKeychain);
		$this->assertArrayNotHasKey('xprv', $bitgoKeychain);
		$this->assertTrue($bitgoKeychain['isBitGo']);
	}

	public function testCreateBitGoEthereumKeychain() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$bitgoKeychain = $bitgo->keychains()->createBitGoKeychain('eth');
		$this->assertArrayHasKey('ethAddress', $bitgoKeychain);
		$this->assertArrayNotHasKey('xpub', $bitgoKeychain);
		$this->assertArrayNotHasKey('xprv', $bitgoKeychain);
		$this->assertArrayNotHasKey('path', $bitgoKeychain);
		$this->assertTrue($bitgoKeychain['isBitGo']);
	}

	public function testCreateBackupKeychain() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$keychain = $bitgo->keychains()->createBackupKeychain('keyternal');
		$this->assertArrayHasKey('path', $keychain);
		$this->assertArrayHasKey('ethAddress', $keychain);
		$this->assertArrayHasKey('xpub', $keychain);
		$this->assertArrayNotHasKey('xprv', $keychain);
	}

}
