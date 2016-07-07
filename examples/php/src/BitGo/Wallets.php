<?php

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

	public function listWallets() {
		return $this->_bitgo->get('wallet');
	}

	public function createWallet($label, $passphrase, $backupXpubProvider = null, $backupXpub = null) {
		$params = [];
		$params['label'] = $label;
		$params['passphrase'] = $passphrase;
		if (!empty($backupXpubProvider)) {
			$params['backupXpubProvider'] = $backupXpubProvider;
		}
		if (!empty($backupXpub)) {
			$params['backupXpub'] = $backupXpub;
		}
		$response = $this->_bitgo->post('wallets/simplecreate', $params);
		$response['wallet'] = new Wallet($this->_bitgo, $response['wallet']);
		return $response;
	}

	public function getWallet($walletID) {
		$rawWallet = $this->_bitgo->get('wallet/' . $walletID);
		return new Wallet($this->_bitgo, $rawWallet);
	}

}