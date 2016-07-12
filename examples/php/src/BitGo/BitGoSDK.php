<?php

namespace BitGo;


use BitGo\eth\Ethereum;
use Unirest\Request;
use Unirest\Response;

class BitGoSDK {

	private $_environment;
	private $_baseURL;

	private $_token = null;

	private $_keychains;
	private $_eth;
	private $_wallets;

	/**
	 * BitGoSDK constructor.
	 */
	public function __construct() {
		$port = 3080;
		$customPort = getenv('PORT');
		if ($customPort && is_numeric($customPort)) {
			$port = $customPort;
		}

		$domain = 'http://localhost';
		$customDomain = getenv('BITGO_CUSTOM_ROOT_URI');
		if (!empty($customDomain)) {
			$domain = $customDomain;
		}

		$this->_baseURL = $domain . ':' . $port;

		$this->_keychains = new Keychains($this);
		$this->_eth = new Ethereum($this);
		$this->_wallets = new Wallets($this);
	}

	/**
	 * Authenticate a BitGoSDK instance using account credentials and the OTP code
	 * @param $username
	 * @param $password
	 * @param string $otp
	 * @return array
	 */
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

	/**
	 * Authenticate using an existing access token
	 * @param $token string access token
	 */
	public function authenticateWithAccessToken($token) {
		$this->_token = $token;
	}

	/**
	 * GET to the BitGo API via Express
	 * @param $api string API path
	 * @return array
	 * @throws \Exception
	 */
	function get($api) {
		$response = Request::get($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders());
		return self::handleResponse($response);
	}

	/**
	 * POST to the BitGo API via Express
	 * @param $api string API path
	 * @param null|string $body request body
	 * @return array
	 * @throws \Exception
	 */
	function post($api, $body = null) {
		$json = $this->prepareRequestBody($body);
		$response = Request::post($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders(), $json);
		return self::handleResponse($response);
	}

	/**
	 * PUT to the BitGo API via Express
	 * @param $api string API path
	 * @param null|string $body request body
	 * @return array
	 * @throws \Exception
	 */
	function put($api, $body = null) {
		$json = $this->prepareRequestBody($body);
		$response = Request::put($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders(), $json);
		return self::handleResponse($response);
	}

	/**
	 * DELETE to the BitGo API via Express
	 * @param $api string API path
	 * @param null|string $body request body
	 * @return array
	 * @throws \Exception
	 */
	function delete($api, $body = null) {
		$json = $this->prepareRequestBody($body);
		$response = Request::delete($this->_baseURL . '/api/v1/' . $api, $this->prepareHeaders(), $json);
		return self::handleResponse($response);
	}

	/**
	 * Prepare the headers for API requests
	 * @return array
	 */
	private function prepareHeaders() {
		$headers = ['content-type' => 'application/json'];
		if ($this->_token) {
			$headers['authorization'] = 'Bearer ' . $this->_token;
		}
		return $headers;
	}

	/**
	 * Prepare request body JSON
	 * @param null $body associative array to send as part of the request
	 * @return null|string
	 */
	private function prepareRequestBody($body = null) {
		if (!empty($body)) {
			return json_encode($body);
		}
		return null;
	}

	/**
	 * @param Response $response
	 * @return array
	 * @throws \Exception
	 */
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

	/**
	 * Get the current active session
	 * @return array
	 * @throws \Exception
	 */
	public function getSession() {
		$this->assertAuthenticated();
		return $this->get('user/session');
	}

	/**
	 * Unlock the current session
	 * @param $otp
	 * @return array
	 * @throws \Exception
	 */
	public function unlock($otp) {
		$this->assertAuthenticated();
		return $this->post('user/unlock', ['otp' => $otp]);
	}

	/**
	 * Lock the current session
	 * @return array
	 * @throws \Exception
	 */
	public function lock() {
		$this->assertAuthenticated();
		return $this->post('user/lock');
	}

	/**
	 * Get keychains utility object
	 * @return Keychains
	 * @throws \Exception
	 */
	public function keychains() {
		$this->assertAuthenticated();
		return $this->_keychains;
	}

	/**
	 * Get Ethereum utility object
	 * @return Ethereum
	 */
	public function eth() {
		return $this->_eth;
	}

	/**
	 * Get wallets utility object
	 * @return Wallets
	 * @throws \Exception
	 */
	public function wallets() {
		$this->assertAuthenticated();
		return $this->_wallets;
	}

	/**
	 * Check if the BitGoSDK instance is authenticated
	 * @return bool
	 */
	public function isAuthenticated() {
		return $this->_token != null;
	}

	/**
	 * Make sure the current BitGoSDK instance is authenticated
	 * @throws \Exception
	 */
	function assertAuthenticated() {
		if (!$this->isAuthenticated()) {
			throw new \Exception('BitGo object needs to be authenticated');
		}
	}

}