<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\DynamicMethodTrait;
use Phlex\Core\Exception;
use Phlex\Core\HookTrait;

/**
 * @coversDefaultClass \Phlex\Core\DynamicMethodTrait
 */
class DynamicMethodTraitTest extends \Phlex\Core\PHPUnit\TestCase
{
    /**
     * Test constructor.
     */
    public function testConstruct()
    {
        $m = new DynamicMethodMock();
        $m->addMethod('test', fn () => 'world');

        $this->assertTrue($m->hasMethod('test'));

        $res = 'Hello, ' . $m->test();
        $this->assertSame('Hello, world', $res);
    }

    public function testException1()
    {
        // can't call undefined method
        $this->expectException(Exception::class);
        $m = new DynamicMethodMock();
        $m->unknownMethod();
    }

    /**
     * Test arguments.
     */
    public function testArguments()
    {
        // simple method
        $m = new DynamicMethodMock();
        $m->addMethod('sum', fn ($m, $a, $b) => $a + $b);
        $res = $m->sum(3, 5);
        $this->assertSame(8, $res);

        $m = new DynamicMethodMock();
        $m->addMethod('getElementCount', \Closure::fromCallable([new ContainerMock(), 'getElementCount']));
        $this->assertSame(0, $m->getElementCount());
    }

    public function testDoubleMethodException()
    {
        $this->expectException(Exception::class);

        $m = new DynamicMethodMock();
        $m->addMethod('sum', fn ($m, $a, $b) => $a + $b);
        $m->addMethod('sum', fn ($m, $a, $b) => $a + $b);
    }

    /**
     * Test removing dynamic method.
     */
    public function testRemoveMethod()
    {
        // simple method
        $m = new DynamicMethodMock();
        $m->addMethod('sum', fn ($m, $a, $b) => $a + $b);
        $this->assertTrue($m->hasMethod(('sum')));
        $m->removeMethod('sum');
        $this->assertFalse($m->hasMethod(('sum')));
    }

    public function testGlobalMethodException1()
    {
        // can't add global method without AppScopeTrait and HookTrait
        $this->expectException(Exception::class);
        $m = new DynamicMethodMock();
        $m->addGlobalMethod('sum', fn ($m, $obj, $a, $b) => $a + $b);
    }

    /**
     * Test adding, checking, removing global method.
     */
    public function testGlobalMethods()
    {
        $app = new GlobalMethodAppMock();

        $m = new GlobalMethodObjectMock();
        $m->setApp($app);

        $m2 = new GlobalMethodObjectMock();
        $m2->setApp($app);

        $m->addGlobalMethod('sum', fn ($m, $obj, $a, $b) => $a + $b);
        $this->assertTrue($m->hasGlobalMethod('sum'));

        $res = $m2->sum(3, 5);
        $this->assertSame(8, $res);

        $m->removeGlobalMethod('sum');
        $this->assertFalse($m2->hasGlobalMethod('sum'));
    }

    public function testDoubleGlobalMethodException()
    {
        $this->expectException(Exception::class);

        $m = new GlobalMethodObjectMock();
        $m->setApp(new GlobalMethodAppMock());

        $m->addGlobalMethod('sum', fn ($m, $obj, $a, $b) => $a + $b);
        $m->addGlobalMethod('sum', fn ($m, $obj, $a, $b) => $a + $b);
    }
}

// @codingStandardsIgnoreStart
class DynamicMethodMock
{
    use DynamicMethodTrait;
    use HookTrait;
}

class GlobalMethodObjectMock
{
    use AppScopeTrait;
    use DynamicMethodTrait;
}

class GlobalMethodAppMock
{
    use HookTrait;
}
// @codingStandardsIgnoreEnd
