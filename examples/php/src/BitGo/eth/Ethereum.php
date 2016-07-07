<?php

namespace BitGo\eth;

use BitGo\BitGoSDK;

class Ethereum {

	private $_bitgo;

	private $_wallets;

	/**
	 * Ethereum constructor.
	 * @param $_bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;

		$this->_wallets = new EthWallets($bitgo);
	}

	public function wallets() {
		$this->_bitgo->assertAuthenticated();
		return $this->_wallets;
	}
}