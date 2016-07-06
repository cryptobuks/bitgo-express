<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 10/28/15
 * Time: 2:58 PM
 */

namespace BitGo;


class Keychains {
	private $_bitgo;

	/**
	 * Keychains constructor.
	 * @param $_bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}

	public function listKeychains() {
		return $this->_bitgo->get('keychain');
	}

	public function createKeychain() {
		return $this->_bitgo->post('keychain/local');
	}

	public function createBitGoKeychain($type = null) {
		$params = null;
		if (!empty($type)) {
			$params = ['type' => $type];
		}
		return $this->_bitgo->post('keychain/bitgo', $params);
	}

	public function createBackupKeychain($provider, $type = null) {
		$params = [];
		$params['provider'] = $provider;
		$params['type'] = $type;
		return $this->_bitgo->post('keychain/backup', $params);
	}

}