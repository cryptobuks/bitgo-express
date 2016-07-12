<?php

namespace BitGo;

/**
 * Class Keychains
 * Access decorator for keychain-related endpoints
 * @package BitGo
 */
class Keychains {
	private $_bitgo;

	/**
	 * Keychains constructor.
	 * @param BitGoSDK $bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}

	/**
	 * List user keychains
	 * @return array
	 */
	public function listKeychains() {
		return $this->_bitgo->get('keychain');
	}

	/**
	 * Create new user keychain
	 * @return array
	 */
	public function createKeychain() {
		return $this->_bitgo->post('keychain/local');
	}

	/**
	 * Create new BitGo keychain
	 * @param null|string $type coin type. Server assumes bitcoin by default if null, can also be eth
	 * @return array
	 */
	public function createBitGoKeychain($type = null) {
		$params = null;
		if (!empty($type)) {
			$params = ['type' => $type];
		}
		return $this->_bitgo->post('keychain/bitgo', $params);
	}

	/**
	 * Create a new backup keychain with a KRS
	 * @param string $provider KRS provider, e. g. keyternal
	 * @param null|string $type coin type. Server assumes bitcoin by default if null, can also be eth
	 * @return array
	 */
	public function createBackupKeychain($provider, $type = null) {
		$params = [];
		$params['provider'] = $provider;
		$params['type'] = $type;
		return $this->_bitgo->post('keychain/backup', $params);
	}

}