<?php

use BitGo\BitGoSDK;

class BitGoSDKTest extends PHPUnit_Framework_TestCase {

	public function testLoginFailure() {
		$bitgo = new BitGoSDK();
		try {
			$bitgo->authenticate('tester@bitgo.com', 'incorrect password');
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(400, $e->getCode());
			$this->assertEquals('User credentials are invalid', $e->getMessage());
		}
	}

	public function testLogin() {
		$bitgo = new BitGoSDK();
		$response = $bitgo->authenticate('tester@bitgo.com', getenv('BITGOJS_TEST_PASSWORD'));
		$this->assertArrayHasKey('access_token', $response);
		$this->assertArrayHasKey('expires_at', $response);
		$this->assertArrayHasKey('expires_in', $response);
		$this->assertArrayHasKey('scope', $response);
		$this->assertArrayHasKey('user', $response);
	}

	public function testUnauthenticatedSession() {
		$bitgo = new BitGoSDK();
		try {
			$bitgo->getSession();
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals('BitGo object needs to be authenticated', $e->getMessage());
		}
	}

	public function testSession() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$session = $bitgo->getSession();
		$this->assertArrayHasKey('session', $session);
		$this->assertArrayHasKey('id', $session['session']);
		$this->assertArrayHasKey('client', $session['session']);
		$this->assertArrayHasKey('user', $session['session']);
		$this->assertArrayHasKey('scope', $session['session']);
		$this->assertArrayHasKey('created', $session['session']);
		$this->assertArrayHasKey('expires', $session['session']);
		$this->assertArrayHasKey('ip', $session['session']);
		$this->assertArrayHasKey('ipRestrict', $session['session']);
		$this->assertArrayHasKey('origin', $session['session']);
		$this->assertArrayHasKey('isExtensible', $session['session']);
	}

	public function testFailedUnlock() {
		$bitgo = TestUtils::authenticateTestBitgo();
		try {
			$bitgo->unlock('1234567');
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(401, $e->getCode());
			$this->assertEquals('incorrect otp', $e->getMessage());
		}
	}

	public function testUnlock() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$unlock = $bitgo->unlock('0000000');
		$this->assertArrayHasKey('session', $unlock);
		$this->assertArrayHasKey('unlock', $unlock['session']);
		$this->assertArrayHasKey('time', $unlock['session']['unlock']);
		$this->assertArrayHasKey('expires', $unlock['session']['unlock']);
		$this->assertArrayHasKey('txValueLimit', $unlock['session']['unlock']);
		$this->assertArrayHasKey('txValue', $unlock['session']['unlock']);
		$bitgo->lock();
	}

}
