<?php

use BitGo\BitGoSDK;

/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 7/5/16
 * Time: 7:41 PM
 */
class TestUtils {

	static function authenticateTestBitgo() {
		$bitgo = new BitGoSDK();
		$bitgo->authenticate('tester@bitgo.com', getenv('BITGOJS_TEST_PASSWORD'));
		return $bitgo;
	}

}