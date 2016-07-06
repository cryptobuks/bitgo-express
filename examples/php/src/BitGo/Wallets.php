<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 10/28/15
 * Time: 2:58 PM
 */

namespace BitGo;


class Wallets {
	private $_bitgo;

	/**
	 * Wallets constructor.
	 * @param $_bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}


}