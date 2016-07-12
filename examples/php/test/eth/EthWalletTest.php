<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik
 * Date: 7/7/16
 * Time: 11:52 AM
 */

namespace eth;


use Exception;
use TestUtils;

class EthWalletTest extends \PHPUnit_Framework_TestCase {
	private static $testWallet;

	private static function getTestWallet() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$memoizedWallet = self::$testWallet;
		if ($memoizedWallet instanceof \BitGo\Wallet) {
			return $memoizedWallet;
		}
		self::$testWallet = $bitgo->eth()->wallets()->getWallet('0x9c532f9a429661e9199d447d63e4f182c16fb593');
		return self::$testWallet;
	}

	public function testCreateAddress() {
		$testWallet = self::getTestWallet();
		$address = $testWallet->createAddress();
		$this->assertEquals($testWallet->getID(), $address['walletAddress']);
		$this->assertNotEmpty($address['address']);
		$this->assertNotEmpty($address['walletNonce']);
		$this->assertNotEmpty($address['deployTxHash']);
	}

	public function testAddressLabels() {
		$testWallet = self::getTestWallet();
		$labelAddress = '0xeab1cac0d744e99289c1798c280961172e63b508';
		$testWallet->setAddressLabel($labelAddress, 'testLabel');
		$labels = $testWallet->listAddressLabels();
		$this->assertCount(1, $labels['labels']);
		$this->assertEquals('testLabel', $labels['labels'][0]['label']);
		$this->assertEquals($testWallet->getID(), $labels['labels'][0]['walletId']);
		$this->assertEquals($labelAddress, $labels['labels'][0]['address']);
		$testWallet->deleteAddressLabel($labelAddress);
	}

	public function testListTransactions() {
		$testWallet = self::getTestWallet();
		$transactions = $testWallet->listTransactions();
		$this->assertArrayHasKey('transactions', $transactions);
		$this->assertArrayHasKey('start', $transactions);
		$this->assertArrayHasKey('count', $transactions);
		$this->assertEquals($transactions['count'], count($transactions['transactions']));
		$firstTx = $transactions['transactions'][0];
		$this->assertNotEmpty($firstTx['from']);
		$this->assertNotEmpty($firstTx['to']);
		$this->assertNotEmpty($firstTx['data']);
		$this->assertNotEmpty($firstTx['txHash']);
		$this->assertNotEmpty($firstTx['nonce']);
		$this->assertNotEmpty($firstTx['gas']);
		$this->assertNotEmpty($firstTx['gasPrice']);

		// confirmations could be 0
		$this->assertArrayHasKey('confirmations', $firstTx);
		// if confirmations < 1, blockhash is undefined
		if ($firstTx['confirmations'] > 0) {
			$this->assertNotEmpty($firstTx['blockHash']);
			$this->assertNotEmpty($firstTx['blockIndex']);
			$this->assertNotEmpty($firstTx['blockHeight']);
			$this->assertNotEmpty($firstTx['confirmTime']);
		}

		$this->assertNotEmpty($firstTx['entries']);
		$firstEntry = $firstTx['entries'][0];
		$this->assertNotEmpty($firstEntry['address']);
		$this->assertNotNull($firstEntry['value']);
	}

	public function testWebhooks() {
		$testWallet = self::getTestWallet();

		$webhooks = $testWallet->listWebhooks();
		$webhookCount = count($webhooks['webhooks']);

		$webhook = $testWallet->createWebhook('transfer', 'https://fakesub.bitgo.com');
		$this->assertNotEmpty($webhook['id']);
		$this->assertNotEmpty($webhook['created']);
		$this->assertEquals($testWallet->getID(), $webhook['walletId']);
		$this->assertEquals('eth', $webhook['coin']);
		$this->assertEquals('transfer', $webhook['type']);
		$this->assertEquals('https://fakesub.bitgo.com', $webhook['url']);

		$webhooks = $testWallet->listWebhooks();
		$this->assertCount($webhookCount + 1, $webhooks['webhooks']);

		$testWallet->deleteWebhook('transfer', 'https://fakesub.bitgo.com');

		$webhooks = $testWallet->listWebhooks();
		$this->assertCount($webhookCount, $webhooks['webhooks']);
	}

	public function testListTransfers() {
		$testWallet = self::getTestWallet();
		$transfers = $testWallet->listTransfers();
		$this->assertArrayHasKey('transfers', $transfers);
		$this->assertArrayHasKey('start', $transfers);
		$this->assertArrayHasKey('count', $transfers);
		$this->assertEquals($transfers['count'], count($transfers['transfers']));
		$firstTransfer = $transfers['transfers'][0];

		// TODO: find out which one it is *supposed* to be
		// $this->assertEquals($testWallet->getID(), $firstTransfer['wallet']);
		$this->assertEquals($testWallet->getRawWallet()['_id'], $firstTransfer['wallet']);

		$this->assertEquals('ETH', $firstTransfer['token']);
		$this->assertNotEmpty($firstTransfer['id']);
		$this->assertNotEmpty($firstTransfer['state']);
		$this->assertNotEmpty($firstTransfer['from']);
		$this->assertNotEmpty($firstTransfer['value']);
		$this->assertNotEmpty($firstTransfer['txHash']);
		$this->assertNotEmpty($firstTransfer['gas']);
		$this->assertNotEmpty($firstTransfer['gasPrice']);

		// confirmations could be 0
		$this->assertNotNull($firstTransfer['confirmations']);
		// if confirmations < 1, blockhash is undefined
		if ($firstTransfer['confirmations'] > 0) {
			$this->assertNotEmpty($firstTransfer['gasUsed']);
			$this->assertNotEmpty($firstTransfer['confirmTime']);
			$this->assertNotEmpty($firstTransfer['blockHeight']);
		}

		$this->assertNotEmpty($firstTransfer['outputs']);
		$firstOutput = $firstTransfer['outputs'][0];
		$this->assertNotEmpty($firstOutput['to']);
		$this->assertNotEmpty($firstOutput['value']);
		// $this->assertArrayHasKey('data', $firstOutput);
	}

	public function testTransactions() {
		$bitgo = TestUtils::authenticateTestBitgo();
		$wallet = $bitgo->eth()->wallets()->getWallet('0x9c532f9a429661e9199d447d63e4f182c16fb593');
		$receiveAddress = $wallet->createAddress()['address'];
		$password = 'test wallet #1 security';

		try {
			$wallet->sendTransaction($receiveAddress, '1234567891098', $password);
			$this->fail();
		} catch (Exception $e) {
			$this->assertEquals(401, $e->getCode());
			$this->assertEquals('needs unlock', $e->getMessage());
		}

		$bitgo->unlock('0000000');
		$transaction = $wallet->sendTransaction($receiveAddress, '1234567891098', $password);
		$bitgo->lock();

		$this->assertNotNull($transaction['tx']);
		$this->assertNotNull($transaction['hash']);
	}

	public function testStaticProperties() {
		$testWallet = self::getTestWallet();
		$this->assertGreaterThan(0, $testWallet->getBalance());
		$this->assertEquals('eth', $testWallet->getType());
	}

}
