<?php

use BitGo\BitGoSDK;

/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 7/5/16
 * Time: 7:41 PM
 */
class TestUtils {

	private static $authenticatedTestBitgo;

	static function authenticateTestBitgo() {
		$memoizedBitgo = self::$authenticatedTestBitgo;
		if ($memoizedBitgo instanceof BitGoSDK && $memoizedBitgo->isAuthenticated()) {
			return $memoizedBitgo;
		}

		$bitgo = new BitGoSDK();
		$bitgo->authenticate('tester@bitgo.com', getenv('BITGOJS_TEST_PASSWORD'));
		self::$authenticatedTestBitgo = $bitgo;
		return $bitgo;
	}

}