<?php

namespace BitGo;

class Wallet {
	private $_bitgo;
	private $_rawWallet;

	/**
	 * Wallet constructor.
	 * @param $_bitgo
	 * @param $_rawWallet
	 */
	public function __construct(BitGoSDK $bitgo, $rawWallet) {
		$this->_bitgo = $bitgo;
		$this->_rawWallet = $rawWallet;
	}

	/**
	 * @return mixed
	 */
	public function getRawWallet() {
		return $this->_rawWallet;
	}

	public function getID() {
		return $this->_rawWallet['id'];
	}

	public function getName() {
		return $this->_rawWallet['label'];
	}

	public function getBalance() {
		return $this->_rawWallet['balance'];
	}

	public function getSpendableBalance() {
		return $this->_rawWallet['spendableBalance'];
	}

	public function getConfirmedBalance() {
		return $this->_rawWallet['confirmedBalance'];
	}

	public function canSendInstant() {
		return isset($this->_rawWallet['canSendInstant']) && !!$this->_rawWallet['canSendInstant'];
	}

	public function getInstantBalance() {
		return $this->_rawWallet['instantBalance'];
	}

	public function getType() {
		return $this->_rawWallet['type'];
	}

	public function createAddress($chain = '0') {
		return $this->_bitgo->post($this->url('address/' . $chain));
	}

	public function listAddresses() {
		return $this->_bitgo->get($this->url('addresses'));
	}

	public function getUnconfirmedSends() {
		return $this->_rawWallet['unconfirmedSends'];
	}

	public function getUnconfirmedReceives() {
		return $this->_rawWallet['unconfirmedReceives'];
	}

	public function getStats() {
		return $this->_bitgo->get($this->url('stats'));
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

	public function listUnspents() {
		return $this->_bitgo->get($this->url('unspents'));
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

	/**
	 * @param $address string receive address
	 * @param $amount integer amount in satoshis
	 * @param null $message string message to pass along
	 * @return mixed
	 */
	public function sendCoins($address, $amount, $passphrase, $message = null) {
		return $this->sendMany([$address => $amount], $passphrase, $message);
	}

	/**
	 * @param $recipients array associative mapping from receive address to amounts in satoshis
	 * @param null $message
	 * @return mixed
	 */
	public function sendMany($recipients, $passphrase, $message = null) {
		$params = [
			'recipients' => $recipients,
			'walletPassphrase' => $passphrase
		];
		if (!empty($message)) {
			$params['message'] = $message;
		}
		return $this->_bitgo->post($this->url('sendmany'), $params);
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
		$url = 'wallet/' . $this->getID();
		if (!empty($extra)) {
			$url .= '/' . $extra;
		}
		return $url;
	}

}