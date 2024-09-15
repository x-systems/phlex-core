<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\ContainerTrait;
use Phlex\Core\Exception;
use Phlex\Core\InitializerTrait;
use Phlex\Core\Phpunit\TestCase;

class InitializerTraitTest extends TestCase
{
    public function testInit(): void
    {
        $m = new InitializerMock();
        self::assertFalse($m->isInitialized());
        $m->initialize();
        self::assertTrue($m->isInitialized());
        self::assertTrue($m->result);
        $m->assertIsInitialized();
    }

    public function testInitCalledFromAdd(): void
    {
        $container = new class() {
            use ContainerTrait;
        };

        $m = new InitializerMock();
        $container->add($m);
        self::assertTrue($m->isInitialized());
        self::assertTrue($m->result);
        $m->assertIsInitialized();
    }

    public function testInitNotCalled(): void
    {
        $m = new InitializerMock();
        self::assertFalse($m->isInitialized());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Object was not initialized');
        $m->assertIsInitialized();
    }

    public function testInitCalledTwiceException(): void
    {
        $m = new InitializerMock();
        $m->initialize();
        self::assertTrue($m->isInitialized());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Object already initialized');
        $m->initialize();
    }

    public function testInitDeclaredPublicException(): void
    {
        $m = new class() extends AbstractInitializerMock {
            #[\Override]
            public function doInitialize(): void {}
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('doInitialize method must have protected visibility');
        $m->initialize();
    }
}

abstract class AbstractInitializerMock
{
    use InitializerTrait;
}

class InitializerMock extends AbstractInitializerMock
{
    /** @var bool */
    public $result = false;

    #[\Override]
    protected function doInitialize(): void
    {
        parent::doInitialize();

        $this->result = true;
    }
}
