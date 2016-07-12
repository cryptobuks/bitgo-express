<?php

namespace BitGo\eth;

use BitGo\BitGoSDK;

/**
 * Class Ethereum
 * Acess decorator for Ethereum-related endpoints
 * @package BitGo\eth
 */
class Ethereum {

	private $_bitgo;
	private $_wallets;

	/**
	 * Ethereum constructor.
	 * @param BitGoSDK $bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
		$this->_wallets = new EthWallets($bitgo);
	}

	/**
	 * Get Ethereum wallet accessor object
	 * @return EthWallets
	 * @throws \Exception
	 */
	public function wallets() {
		$this->_bitgo->assertAuthenticated();
		return $this->_wallets;
	}
}