<?php

class BitGoSDK {

    // const BITGO_API_ENDPOINT_PRODUCTION = 'https://bitgo.com/api/v1'; // this will probably never be used
    // const BITGO_API_ENDPOINT_TEST = 'https://test.bitgo.com/api/v1'; // this will probably never be used
    const BITGO_EXPRESS_ENDPOINT = 'http://localhost:3080/api/v1';

    private $username;
    private $hmac;
    private $accessToken;

    public function __construct() {
        // assert that BitGo express is open
        $socket = fsockopen('127.0.0.1', 3080, $errno, $errstr, 5);
        if (!$socket) {
            throw new Exception('Cannot connect to BitGo Express. Please make sure you have a local instance of BitGo Express running. The following error has occurred: ' . $errstr, $errno);
        }

    }

    /**
     * @param $username String username or email
     * @param $password String password
     * @param $otp String one-time-password
     * @return mixed Parsed response JSON
     */
    public function login($username, $password, $otp) {
        $this->username = $username;
        $this->hmac = hash_hmac('sha256', $password, $this->username);

        $curl = curl_init(self::BITGO_EXPRESS_ENDPOINT . '/user/login');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
            [
                'email' => $this->username,
                'password' => $this->hmac,
                'otp' => $otp
            ]
        ));
        $responseString = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($responseString, true);
        $this->accessToken = $response['access_token'];

        return $response;
    }

    /**
     * @param $otp String one-time-password
     * @return mixed Parsed response JSON
     */
    public function unlock($otp) {
        $curl = curl_init(self::BITGO_EXPRESS_ENDPOINT . '/user/unlock');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
            [
                'otp' => '0000000'
            ]
        ));
        $responseString = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($responseString, true);
        return $response;
    }

    /**
     * @return mixed JSON of user's wallets
     */
    public function listWallets() {
        $curl = curl_init(self::BITGO_EXPRESS_ENDPOINT . '/wallet');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($responseString, true);
        return $response;
    }

    /**
     * @param $senderWalletID String ID of the sending wallet
     * @param $walletPassphrase String passphrase for the wallet
     * @param $destinationAddress String Bitcoin address of the recipient
     * @param $amount int Number of satoshis to be sent. Needs to be an integer
     * @return mixed Parsed response JSON
     */
    public function sendCoins($senderWalletID, $walletPassphrase, $destinationAddress, $amount) {
        $curl = curl_init(self::BITGO_EXPRESS_ENDPOINT . '/wallet/' . urlencode($senderWalletID) . '/sendcoins');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
            [
                'walletPassphrase' => $walletPassphrase,
                'address' => $destinationAddress,
                'amount' => $amount,
                'otp' => '0000000'
            ]
        ));
        $responseString = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($responseString, true);
        return $response;
    }

}
