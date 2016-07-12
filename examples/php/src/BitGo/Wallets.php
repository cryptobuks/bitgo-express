<?php

namespace BitGo;

/**
 * Class Wallets
 * Access decorator for Bitcoin wallets
 * @package BitGo
 */
class Wallets {
	private $_bitgo;

	/**
	 * Wallets constructor.
	 * @param BitGoSDK $bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}

	/**
	 * List Bitcoin wallets
	 * @return array
	 */
	public function listWallets() {
		return $this->_bitgo->get('wallet');
	}

	/**
	 * @param $label string wallet name/label
	 * @param $passphrase string wallet passphrase
	 * @param null|string $backupXpubProvider KRS provider, e. g. keyternal
	 * @param null|string $backupXpub backup xpub
	 * @return array
	 */
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

	/**
	 * Get a wallet by its ID
	 * @param $walletID string Bitcoin wallet ID
	 * @return Wallet
	 */
	public function getWallet($walletID) {
		$rawWallet = $this->_bitgo->get('wallet/' . $walletID);
		return new Wallet($this->_bitgo, $rawWallet);
	}

}