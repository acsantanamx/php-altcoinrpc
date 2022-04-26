<?php

namespace DOne\Altcoin\Tests;

use DOne\Altcoin\Client as AltcoinClient;
use DOne\Altcoin\Config;
use DOne\Altcoin\Exceptions;
use DOne\Altcoin\Responses\AltcoindResponse;
use DOne\Altcoin\Responses\Response;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ClientTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->altcoind = new AltcoinClient();
    }

    /**
     * Test client getter and setter.
     *
     * @return void
     */
    public function testClientSetterGetter(): void
    {
        $altcoind = new AltcoinClient('http://old_client.org');
        $this->assertInstanceOf(AltcoinClient::class, $altcoind);

        $base_uri = $altcoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'old_client.org');

        $oldClient = $altcoind->getClient();
        $this->assertInstanceOf(GuzzleHttp::class, $oldClient);

        $newClient = new GuzzleHttp(['base_uri' => 'http://new_client.org']);
        $altcoind->setClient($newClient);

        $base_uri = $altcoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'new_client.org');
    }

    /**
     * Test preserve method name case config option.
     *
     * @return void
     */
    public function testPreserveCaseOption(): void
    {
        $altcoind = new AltcoinClient(['preserve_case' => true]);
        $altcoind->setClient($this->mockGuzzle([$this->getBlockResponse()]));
        $altcoind->getBlockHeader();

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getBlockHeader',
            $request['id']
        ), $request);
    }

    /**
     * Test client config getter.
     *
     * @return void
     */
    public function testGetConfig(): void
    {
        $this->assertInstanceOf(Config::class, $this->altcoind->getConfig());
    }

    /**
     * Test simple request.
     *
     * @return void
     */
    public function testRequest(): void
    {
        $response = $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
        $this->assertEquals(self::$getBlockResponse, $response->get());
    }

    /**
     * Test multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletRequest(): void
    {
        $wallet = 'testwallet.dat';

        $response = $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBalanceResponse()]))
            ->wallet($wallet)
            ->request('getbalance');

        $this->assertEquals(self::$balanceResponse, $response->get());
        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletAsyncRequest(): void
    {
        $wallet = 'testwallet2.dat';

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBalanceResponse()]))
            ->wallet($wallet)
            ->requestAsync('getbalance', []);

        $this->altcoind->wait();

        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async request.
     *
     * @return void
     */
    public function testAsyncRequest(): void
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (AltcoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->requestAsync(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->altcoind->wait();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testMagic(): void
    {
        $response = $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeader(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testAsyncMagic(): void
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (AltcoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeaderAsync(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->altcoind->wait();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test altcoind exception.
     *
     * @return void
     */
    public function testAltcoindException(): void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test request exception with error code.
     *
     * @return void
     */
    public function testRequestExceptionWithServerErrorCode(): void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test request exception with empty response body.
     *
     * @return void
     */
    public function testRequestExceptionWithEmptyResponseBody(): void
    {
        $this->expectException(Exceptions\ConnectionException::class);
        $this->expectExceptionMessage($this->error500());
        $this->expectExceptionCode(500);

        $this->altcoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with empty response body.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithEmptyResponseBody(): void
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == $this->error500() &&
                    $exception->getCode() == 500;
            }),
        ]);

        $this->altcoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->altcoind->wait();
    }

    /**
     * Test request exception with response.
     *
     * @return void
     */
    public function testRequestExceptionWithResponseBody(): void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithResponseBody(): void
    {
        $onRejected = $this->mockCallable([
            $this->callback(function (Exceptions\BadRemoteCallException $exception) {
                return $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $this->altcoind->wait();
    }

    /**
     * Test request exception with no response.
     *
     * @return void
     */
    public function testRequestExceptionWithNoResponseBody(): void
    {
        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with no response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithNoResponseBody(): void
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == 'test' &&
                    $exception->getCode() == 0;
            }),
        ]);

        $this->altcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->altcoind->wait();
    }

    /**
     * Test setting different response handler class.
     *
     * @return void
     */
    public function testSetResponseHandler(): void
    {
        $fake = new FakeClient();

        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ], $fake->getClient()->getConfig('handler'));

        $response = $fake
            ->setClient($guzzle)
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $this->assertInstanceOf(FakeResponse::class, $response);
    }
}

class FakeClient extends AltcoinClient
{
    /**
     * Gets response handler class name.
     *
     * @return string
     */
    protected function getResponseHandler(): string
    {
        return 'DOne\\Altcoin\\Tests\\FakeResponse';
    }
}

class FakeResponse extends Response
{
    //
}
