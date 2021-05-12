<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core;
use Phlex\Core\Exception;

/**
 * @coversDefaultClass \Phlex\Core\InitializerTrait
 */
class InitializerTraitTest extends \Phlex\Core\PHPUnit\TestCase
{
    /**
     * Test constructor.
     */
    public function testBasic()
    {
        $m = new ContainerMock2();
        $i = $m->add(new InitializerMock());

        $this->assertTrue($i->result);
    }

    public function testInitializedTwice()
    {
        $this->expectException(Exception::class);
        $m = new InitializerMock();
        $m->initialize();
        $m->initialize();
    }
}

// @codingStandardsIgnoreStart
class ContainerMock2
{
    use core\ContainerTrait;
}

class _InitializerMock
{
    use core\InitializerTrait;
}

class InitializerMock extends _InitializerMock
{
    public $result = false;

    protected function doInitialize(): void
    {
        parent::doInitialize();

        $this->result = true;
    }
}
// @codingStandardsIgnoreEnd
