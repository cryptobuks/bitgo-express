<?php

namespace BitGo\eth;

use BitGo\BitGoSDK;

/**
 * Class EthWallets
 * Access decorator for Ethereum wallets
 * @package BitGo\eth
 */
class EthWallets {

	private $_bitgo;

	/**
	 * EthWallets constructor.
	 * @param BitGoSDK $bitgo
	 */
	public function __construct(BitGoSDK $bitgo) {
		$this->_bitgo = $bitgo;
	}

	/**
	 * List Ethereum wallets
	 * @return array
	 */
	public function listWallets() {
		$this->_bitgo->assertAuthenticated();
		return $this->_bitgo->get('eth/wallet');
	}

	/**
	 * Generate a new Ethereum wallet
	 * @param $label string wallet name/label
	 * @param $passphrase string wallet passphrase
	 * @param null|string $backupXpubProvider KRS provider, e. g. keyternal
	 * @param null|string $backupAddress backup Ethereum address
	 * @param null|string $backupXpub backup xpub
	 * @return array
	 */
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

	/**
	 * Get a wallet by its ID
	 * @param $walletID string Ethereum wallet ID
	 * @return EthWallet
	 */
	public function getWallet($walletID) {
		$this->_bitgo->assertAuthenticated();
		$rawEthWallet = $this->_bitgo->get('eth/wallet/' . $walletID);
		return new EthWallet($this->_bitgo, $rawEthWallet);
	}
}