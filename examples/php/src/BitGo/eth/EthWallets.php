<?php

namespace BitGo\eth;

use BitGo\BitGoSDK;

class EthWallets {

	private $_bitgo;

	/**
	 * EthWallets constructor.
	 * @param $_bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}

	public function generateWallet($label, $passphrase, $backupXpubProvider = null, $backupAddress = null, $backupXpub = null) {
		$params = [];
		$params['label'] = $label;
		$params['passphrase'] = $passphrase;
		$params['type'] = 'eth';
		if (!empty($backupXpubProvider)) {
			$params['backupXpubProvider'] = $backupXpubProvider;
		}
		if (!empty($backupAddress)) {
			$params['backupAddress'] = $backupAddress;
		}
		if (!empty($backupXpub)) {
			$params['backupXpub'] = $backupXpub;
		}
		$response = $this->_bitgo->post('eth/wallet/generate', $params);
		$response['wallet'] = new EthWallet($this->_bitgo, $response['wallet']);
		return $response;
	}

	public function getWallet($walletID) {
		$rawEthWallet = $this->_bitgo->get('eth/wallet/' . $walletID);
		return new EthWallet($this->_bitgo, $rawEthWallet);
	}
}