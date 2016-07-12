<?php

namespace BitGo\eth;

use BitGo\BitGoSDK;

class EthWallet {
	private $_bitgo;
	private $_rawEthWallet;

	/**
	 * Wallet constructor.
	 * @param $_bitgo
	 * @param $_rawWallet
	 */
	public function __construct(BitGoSDK $bitgo, $rawWallet) {
		$this->_bitgo = $bitgo;
		$this->_rawEthWallet = $rawWallet;
	}

	/**
	 * @return mixed
	 */
	public function getRawWallet() {
		return $this->_rawEthWallet;
	}

	public function getID() {
		return $this->_rawEthWallet['id'];
	}

	public function getSigningAddresses(){
		return $this->_rawEthWallet['private']['addresses'];
	}

	public function getName() {
		return $this->_rawEthWallet['label'];
	}

	public function getBalance() {
		return $this->_rawEthWallet['balance'];
	}

	public function getType() {
		return $this->_rawEthWallet['type'];
	}

	public function createAddress() {
		return $this->_bitgo->post($this->url('address'));
	}

	public function getAddress($address) {
		return $this->_bitgo->get($this->url('addresses/' . $address));
	}

	public function setName($name) {
		$params = ['label' => $name];
		return $this->_bitgo->put($this->url(), $params);
	}

	public function listAddressLabels() {
		return $this->_bitgo->get('labels/' . $this->getID());
	}

	public function setAddressLabel($address, $label) {
		$params = ['label' => $label];
		return $this->_bitgo->put('labels/' . $this->getID() . '/' . $address, $params);
	}

	public function deleteAddressLabel($address) {
		return $this->_bitgo->delete('labels/' . $this->getID() . '/' . $address);
	}

	public function listTransactions() {
		return $this->_bitgo->get($this->url('tx'));
	}

	public function getTransaction($transactionID) {
		return $this->_bitgo->get($this->url('tx/' . $transactionID));
	}

	public function getTransactionBySequenceID($sequenceID) {
		return $this->_bitgo->get($this->url('tx/sequence/' . $sequenceID));
	}

	public function sendTransaction($address, $amount, $passphrase) {
		$params = [];
		$params['recipients'] = [
			['toAddress' => $address, 'value' => $amount]
		];
		$params['walletPassphrase'] = $passphrase;
		return $this->_bitgo->post($this->url('sendtransaction'), $params);
	}

	public function listTransfers() {
		return $this->_bitgo->get($this->url('transfer'));
	}

	public function getTransfer($transferID) {
		return $this->_bitgo->get($this->url('transfer/' . $transferID));
	}

	public function listWebhooks() {
		return $this->_bitgo->get($this->url('webhooks'));
	}

	public function createWebhook($type, $url) {
		$params = [
			'type' => $type,
			'url' => $url
		];
		return $this->_bitgo->post($this->url('webhooks'), $params);
	}

	public function deleteWebhook($type, $url) {
		$params = [
			'type' => $type,
			'url' => $url
		];
		return $this->_bitgo->delete($this->url('webhooks'), $params);
	}

	public function delete() {
		return $this->_bitgo->delete($this->url());
	}

	public function freeze($duration = null) {
		$params = null;
		if (is_numeric($duration)) {
			$params = ['duration' => $duration];
		}
		return $this->_bitgo->post($this->url('freeze'), $params);
	}

	private function url($extra = null) {
		$extra = trim(trim($extra), '/'); // remove leading and trailing whitespace and slashes
		$url = 'eth/wallet/' . $this->getID();
		if (!empty($extra)) {
			$url .= '/' . $extra;
		}
		return $url;
	}

}