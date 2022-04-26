<?php

namespace DOne\Altcoin\Tests\Exceptions;

use DOne\Altcoin\Exceptions;
use DOne\Altcoin\Exceptions\Handler as ExceptionHandler;
use DOne\Altcoin\Tests\TestCase;
use Exception;

class HandlerTest extends TestCase
{
    /**
     * Cleans-up test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Remove all added handlers.
        ExceptionHandler::clearInstance();
    }

    /**
     * Test singleton instantiation.
     *
     * @return void
     */
    public function testSingleton(): void
    {
        $this->assertInstanceOf(
            ExceptionHandler::class,
            ExceptionHandler::getInstance()
        );
    }

    /**
     * Test handler registration.
     *
     * @return void
     */
    public function testRegisterHandler(): void
    {
        ExceptionHandler::getInstance()->registerHandler(function ($exception) {
            $this->assertEquals('Test message', $exception->getMessage());
        });

        $this->expectException(Exception::class);

        ExceptionHandler::getInstance()->handle(new Exception('Test message'));
    }

    /**
     * Test exception namespace setter.
     *
     * @return void
     */
    public function testSetNamespace(): void
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Test message');

        ExceptionHandler::getInstance()->setNamespace('DOne\\Altcoin\\Tests\\Exceptions');
        ExceptionHandler::getInstance()->handle(
            new Exceptions\BadConfigurationException(['foo' => 'bar'], 'Test message')
        );
    }
}

class BadConfigurationException extends Exceptions\BadConfigurationException
{
    //
}
