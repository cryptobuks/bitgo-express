<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 10/28/15
 * Time: 11:53 AM
 */

namespace BitGo;


use Unirest\Request;
use Unirest\Response;

class BitGoSDK {

	private $_environment;
	private $_baseURL;

	private $_token = null;

	private $_keychains;
	private $_wallets;

	/**
	 * BitGoSDK constructor.
	 */
	public function __construct() {
		$port = 3080;
		$customPort = getenv('port');
		if ($customPort && is_numeric($customPort)) {
			$port = $customPort;
		}
		$this->_baseURL = 'http://localhost:' . $port;
		
		$this->_keychains = new Keychains($this);
		$this->_wallets = new Wallets($this);
	}

	public function authenticate($username, $password, $otp = '0000000') {
		$hmac = hash_hmac('sha256', $password, $username);
		$params = [
			'email' => $username,
			'password' => $hmac,
			'otp' => $otp
		];
		$response = $this->post('user/login', $params);
		$this->_token = $response['access_token'];

		return $response;
	}

	function get($api) {
		$response = Request::get($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders());
		return self::handleResponse($response);
	}

	function post($api, $body = null) {
		$headers = ['content-type' => 'application/json'];
		if ($this->_token) {
			$headers['authentication'] = 'Bearer ' . $this->_token;
		}

		$json = null;
		if (!empty($body)) {
			$json = json_encode($body);
		}
		$response = Request::post($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders(), $json);
		return self::handleResponse($response);
	}

	private function prepareHeaders() {
		$headers = ['content-type' => 'application/json'];
		if ($this->_token) {
			$headers['authorization'] = 'Bearer ' . $this->_token;
		}
		return $headers;
	}

	private static function handleResponse(Response $response) {
		assert($response instanceof Response);

		$json = json_decode($response->raw_body, true);

		if ($response->code < 200 || $response->code >= 300) {
			$error = $json['error'];
			if (isset($json['message']) && !empty($json['message'])) {
				// error is always defined, but message is more descriptive
				$error = $json['message'];
			}
			throw new \Exception($error, $response->code);
		}

		return $json;
	}

	public function getSession() {
		$this->assertAuthenticated();
		return $this->get('user/session');
	}

	public function unlock($otp){
		$this->assertAuthenticated();
		return $this->post('user/unlock', ['otp' => $otp]);
	}

	public function keychains() {
		$this->assertAuthenticated();
		return $this->_keychains;
	}

	public function wallets() {
		$this->assertAuthenticated();
		return $this->_wallets;
	}

	public function isAuthenticated() {
		return $this->_token != null;
	}

	private function assertAuthenticated() {
		if (!$this->isAuthenticated()) {
			throw new \Exception('BitGo object needs to be authenticated');
		}
	}

}