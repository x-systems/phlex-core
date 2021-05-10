<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\DiContainerTrait;
use Phlex\Core\Exception;

/**
 * @coversDefaultClass \Phlex\Core\DiContainerTrait
 */
class DiContainerTraitTest extends \Phlex\Core\PHPUnit\TestCase
{
    public function testFromSeed()
    {
        $this->assertSame(StdSat::class, get_class(StdSat::fromSeed([StdSat::class])));
        $this->assertSame(StdSat2::class, get_class(StdSat::fromSeed([StdSat2::class])));

        $this->expectException(Exception::class);
        StdSat2::fromSeed([StdSat::class]);
    }

    public function testNoPropExNumeric()
    {
        $this->expectException(\Error::class);
        $m = new FactoryDiMock2();
        $m->setDefaults([5 => 'qwerty']);
    }

    public function testNoPropExStandard()
    {
        $this->expectException(Exception::class);
        $m = new FactoryDiMock2();
        $m->setDefaults(['not_exist' => 'qwerty']);
    }

    public function testProperties()
    {
        $m = new FactoryDiMock2();

        $m->setDefaults(['a' => 'foo', 'c' => 'bar']);
        $this->assertSame([$m->a, $m->b, $m->c], ['foo', 'BBB', 'bar']);

        $m = new FactoryDiMock2();
        $m->setDefaults(['a' => null, 'c' => false]);
        $this->assertSame([$m->a, $m->b, $m->c], ['AAA', 'BBB', false]);
    }

    public function testPropertiesPassively()
    {
        $m = new FactoryDiMock2();

        $m->setDefaults(['a' => 'foo', 'c' => 'bar'], true);
        $this->assertSame([$m->a, $m->b, $m->c], ['AAA', 'BBB', 'bar']);

        $m = new FactoryDiMock2();
        $m->setDefaults(['a' => null, 'c' => false], true);
        $this->assertSame([$m->a, $m->b, $m->c], ['AAA', 'BBB', false]);

        $m = new FactoryDiMock2();
        $m->a = ['foo'];
        $m->setDefaults(['a' => ['bar']], true);
        $this->assertSame([$m->a, $m->b, $m->c], [['foo'], 'BBB', null]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPassively()
    {
        $m = new FactoryDiMock2();
        $m->setDefaults([], true);
    }

    public function testInstanceOfBeforeConstructor()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Seed class is not a subtype of static class');
        FactoryDiMockConstructorMustNeverBeCalled2::fromSeed([FactoryDiMockConstructorMustNeverBeCalled::class]);
    }

    public function testPropertySetter()
    {
        $m = new FactoryDiMock2();

        $m->setDefaults(['testSetter' => 'value']);
        $this->assertSame([$m->testSetter], ['correct_value']);

        $m->setDefaults(['testSetter' => 'new_value'], true);
        $this->assertSame([$m->testSetter], ['correct_value']);
    }
}

// @codingStandardsIgnoreStart
class FactoryDiMock2
{
    use DiContainerTrait;

    public $a = 'AAA';
    public $b = 'BBB';
    public $c;
    public $testSetter;

    public function setTestSetter($value)
    {
        $this->testSetter = 'correct_' . $value;

        return $this;
    }
}

class FactoryDiMockConstructorMustNeverBeCalled
{
    public function __construct()
    {
        throw new \Error('Contructor must never be called');
    }
}

class FactoryDiMockConstructorMustNeverBeCalled2 extends FactoryDiMockConstructorMustNeverBeCalled
{
    use DiContainerTrait;
}
// @codingStandardsIgnoreEnd
