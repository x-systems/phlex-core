<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\Exception;
use Phlex\Core\Factory;
use Phlex\Core\HookBreaker;
use Phlex\Core\InjectableTrait;

/**
 * @coversDefaultClass \Phlex\Core\Factory
 */
class FactoryTest extends \Phlex\Core\PHPUnit\TestCase
{
    public function testMerge1()
    {
        // string become array
        $this->assertSame(
            ['hello'],
            Factory::mergeSeeds(['hello'], null)
        );

        // left-most value is used
        $this->assertSame(
            ['one'],
            Factory::mergeSeeds(['one'], ['two'], ['three'], ['four'])
        );

        // nulls are ignored
        $this->assertSame(
            ['two'],
            Factory::mergeSeeds(null, ['two'], ['three'], ['four'])
        );

        // object takes precedence
        $o = new FactoryTestDiMock();
        $this->assertSame(
            $o,
            Factory::mergeSeeds(null, ['two'], $o, ['four'])
        );

        // if more than one object, leftmost is returned
        $o1 = new FactoryTestDiMock();
        $o2 = new FactoryTestDiMock();
        $this->assertSame(
            $o2,
            Factory::mergeSeeds(null, ['two'], $o2, $o1, ['four'])
        );
    }

    public function testMerge2()
    {
        // array argument merging
        $this->assertSame(
            ['a1', 'a2'],
            Factory::mergeSeeds(['a1', 'a2'], null, ['b1', 'b2'])
        );

        // nulls are ignored
        $this->assertSame(
            ['b1', 'a2', 'c3'],
            Factory::mergeSeeds([null, 'a2', null], ['b1'], ['c1', null, 'c3'])
        );

        // object takes precedence
        $o = new FactoryTestDiMock();
        $this->assertSame(
            $o,
            Factory::mergeSeeds(['a1'], $o)
        );

        // is object is wrapped in array - we dont care
        $o = new FactoryTestDiMock();
        $this->assertSame(
            ['b1', 'a2', 'c3'],
            Factory::mergeSeeds([null, 'a2', null], ['b1'], ['c1', null, 'c3'], [1 => $o])
        );

        // but constructor arguments (except silently ignored class name)
        // for already instanced object are not valid
        $o = new FactoryTestDiMock();
        $this->expectException(Exception::class);
        Factory::mergeSeeds(['a1', 'a2'], $o);
    }

    public function testMerge3()
    {
        // key/value support
        $this->assertSame(
            ['a' => 1],
            Factory::mergeSeeds(['a' => 1], ['a' => 2])
        );

        // values has no special treatment
        $this->assertSame(
            ['a' => [1]],
            Factory::mergeSeeds(['a' => [1]], ['a' => 2])
        );

        // object is injected with values
        $o = new FactoryTestDiMock();
        $oo = Factory::mergeSeeds(['foo' => 1], $o);

        $this->assertSame($o, $oo);
        $this->assertSame(1, $oo->foo);

        // even it already has value
        $o = new FactoryTestDiMock();
        $o->foo = 5;
        $oo = Factory::mergeSeeds(['foo' => 1], $o);

        $this->assertSame($o, $oo);
        $this->assertSame(1, $oo->foo);

        // but this way existing value is respected
        $o = new FactoryTestDiMock();
        $o->foo = 5;
        $oo = Factory::mergeSeeds($o, ['foo' => 1]);

        $this->assertSame($o, $oo);
        $this->assertSame(5, $oo->foo);
    }

    public function testMerge4()
    {
        // array values don't overwrite but rather merge
        $o = new FactoryTestViewMock();
        $o->foo = ['red'];
        $oo = Factory::mergeSeeds(['foo' => ['green']], $o);

        $this->assertSame($o, $oo);
        $this->assertSame(['red', 'green'], $oo->foo);

        // still we don't care if they are to the right of the object
        $o = new FactoryTestDiMock();
        $o->foo = ['red'];
        $oo = Factory::mergeSeeds($o, ['foo' => ['green']]);

        $this->assertSame($o, $oo);
        $this->assertSame(['red'], $oo->foo);
    }

    public function testMerge5()
    {
        // works even if more arguments present
        $o = new FactoryTestViewMock();
        $o->foo = ['red'];
        $oo = Factory::mergeSeeds(['foo' => ['green']], $o);
        $oo = Factory::mergeSeeds(['foo' => ['xx']], $o);

        $this->assertSame($o, $oo);
        $this->assertSame(['red', 'green', 'xx'], $oo->foo);

        // also without arrays
        $o = new FactoryTestDiMock();
        $o->foo = 'red';
        $oo = Factory::mergeSeeds(['foo' => 'xx'], ['foo' => 'green'], $o, ['foo' => 5]);

        $this->assertSame($o, $oo);
        $this->assertSame('xx', $oo->foo);
    }

    public function testMerge5b()
    {
        // and even if multiple objects are found
        $o = new FactoryTestViewMock();
        $o->foo = ['red'];
        $o2 = new FactoryTestViewMock();
        $o2->foo = ['yellow'];
        $oo = Factory::mergeSeeds(['foo' => ['xx']], $o, ['foo' => ['green']], $o2, ['foo' => ['cyan']]);

        $this->assertSame($o, $oo);
        $this->assertSame(['red', 'xx'], $oo->foo);
    }

    public function testMerge6()
    {
        $oo = Factory::mergeSeeds(['4' => 'four'], ['5' => 'five']);
        $this->assertSame(['4' => 'four', '5' => 'five'], $oo);

        $oo = Factory::mergeSeeds(['4' => ['four']], ['5' => ['five']]);
        $this->assertSame(['4' => ['four'], '5' => ['five']], $oo);

        $oo = Factory::mergeSeeds(['4' => 'four'], ['5' => 'five'], ['6' => 'six']);
        $this->assertSame(['4' => 'four', '5' => 'five', '6' => 'six'], $oo);

        $oo = Factory::mergeSeeds(['x' => ['four']], ['x' => ['five']]);
        $this->assertSame(['x' => ['four']], $oo);

        $oo = Factory::mergeSeeds(['4' => ['four']], ['4' => ['five']]);
        $this->assertSame(['4' => ['four']], $oo);

        $oo = Factory::mergeSeeds(['4' => ['200']], ['4' => ['201']]);
        $this->assertSame(['4' => ['200']], $oo);
    }

    public function testMergeFail1()
    {
        // works even if more arguments present
        $this->expectException(Exception::class);
        $o = new FactoryTestMock();
        $o->foo = ['red'];
        $oo = Factory::mergeSeeds($o, ['foo' => 5]);

        $this->assertSame($o, $oo);
        $this->assertSame(['red', 'green', 'xx'], $oo->foo);
    }

    public function testMergeFail2()
    {
        // works even if more arguments present
        $this->expectException(Exception::class);
        $o = new FactoryTestMock();
        $o->foo = ['red'];
        $oo = Factory::mergeSeeds(['foo' => ['xx']], ['foo' => ['green']], $o);

        $this->assertSame($o, $oo);
        $this->assertSame(['red', 'green', 'xx'], $oo->foo);
    }

    public function testBasic()
    {
        $s1 = Factory::factory([FactoryTestMock::class]);
        $this->assertEmpty($s1->args);

        $s1 = Factory::factory(new FactoryTestMock());
        $this->assertEmpty($s1->args);

        $s1 = Factory::factory(new FactoryTestMock('hello', 'world'));
        $this->assertSame(['hello', 'world'], $s1->args);

        $s1 = Factory::factory(new FactoryTestMock(null, 'world'));
        $this->assertSame([null, 'world'], $s1->args);
    }

    public function testInjection()
    {
        $s1 = Factory::factory(new FactoryTestDiMock(), null);
        $this->assertNotSame('bar', $s1->foo);

        $s1 = Factory::factory(new FactoryTestDiMock(), ['foo' => 'bar']);
        $this->assertSame('bar', $s1->foo);
    }

    public function testArguments()
    {
        /*
        $s1 = Factory::factory([FactoryTestMock::class, 'hello']);
        $this->assertEquals(['hello'], $s1->args);

        $s1 = Factory::factory([FactoryTestMock::class, 'hello', 'world']);
        $this->assertEquals(['hello', 'world'], $s1->args);
         */

        $s1 = Factory::factory([FactoryTestMock::class, null, 'world']);
        $this->assertSame([null, 'world'], $s1->args);

        $s1 = Factory::factory([FactoryTestDiMock::class, 'hello', 'foo' => 'bar', 'world']);
        $this->assertSame(['hello', 'world'], $s1->args);
        $this->assertSame('bar', $s1->foo);
    }

    public function testDefaults()
    {
        $s1 = Factory::factory([FactoryTestDiMock::class, 'hello', 'foo' => 'bar', 'world'], ['more', 'baz' => '', 'more', 'args']);
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['hello', 'world', 'args'], $s1->args);
        $this->assertSame('bar', $s1->foo);
        $this->assertSame('', $s1->baz);

        $s1->setDefaults([]);
    }

    public function testNull()
    {
        $s1 = Factory::factory([FactoryTestDiMock::class, 'foo' => null, null, 'world'], ['more', 'foo' => 'bar', 'more', 'args']);
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['more', 'world', 'args'], $s1->args);
        $this->assertSame('bar', $s1->foo);

        $s1 = Factory::factory(Factory::mergeSeeds([FactoryTestDiMock::class, 'foo' => null, null, 'world'], [FactoryTestMock::class, 'more', 'foo' => 'bar', 'more', 'args']));
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['more', 'world', 'args'], $s1->args);
        $this->assertSame('bar', $s1->foo);

        $s1 = Factory::factory(Factory::mergeSeeds([null, 'foo' => null, null, 'world'], [FactoryTestDiMock::class, 'more', 'foo' => 'bar', 'more', 'args']));
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['more', 'world', 'args'], $s1->args);
        $this->assertSame('bar', $s1->foo);

        $s1 = Factory::factory(Factory::mergeSeeds(null, [FactoryTestDiMock::class, 'more', 'foo' => 'bar', 'more', 'args']));
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['more', 'more', 'args'], $s1->args);
        $this->assertSame('bar', $s1->foo);

        $s1 = Factory::factory(Factory::mergeSeeds([], [FactoryTestDiMock::class, 'test']));
        $this->assertSame(['test'], $s1->args);
    }

    public function testDefaultsObject()
    {
        // $this->expectException(Exception::class);
        $this->expectDeprecation(); // replace with line above once support is removed (expected in 2020-dec)
        $s1 = Factory::factory([new FactoryTestDiMock(), 'foo' => 'bar'], ['baz' => '', 'foo' => 'default']);
    }

    public function testMerge()
    {
        $s1 = Factory::factory([FactoryTestDiMock::class, 'hello', 'world'], ['more', 'more', 'args']);
        $this->assertSame(['hello', 'world', 'args'], $s1->args);

        $s1 = Factory::factory([FactoryTestDiMock::class, null, 'world'], ['more', 'more', 'args']);
        $this->assertSame(['more', 'world', 'args'], $s1->args);
    }

    public function testMerge7()
    {
        $s1 = Factory::mergeSeeds(new FactoryTestDefMock(), ['foo']);
        $this->assertNull($s1->def);
    }

    public function testMerge8()
    {
        $s1 = Factory::mergeSeeds(['foo', null, 'arg'], []);
        $this->assertSame(['foo', null, 'arg'], $s1);
    }

    public function testSeedMustBe()
    {
        $this->expectException(Exception::class);
        $s1 = Factory::factory([], ['foo' => 'bar']);
    }

    public function testClassMayNotBeEmpty()
    {
        $this->expectException(\Error::class);
        $s1 = Factory::factory([''], [FactoryTestDiMock::class, 'test']);
    }

    public function testMystBeDi()
    {
        $this->expectException(Exception::class);
        $s1 = Factory::factory([FactoryTestMock::class, 'hello', 'foo' => 'bar', 'world']);
    }

    public function testMustHaveProperty()
    {
        $this->expectException(Exception::class);
        $s1 = Factory::factory([FactoryTestDiMock::class, 'hello', 'xxx' => 'bar', 'world']);
    }

    public function testGiveClassFirst()
    {
        $this->expectException(Exception::class);
        $s1 = Factory::factory(['foo' => 'bar'], new FactoryTestDiMock());
    }

    public function testStringDefault()
    {
        $s1 = Factory::factory([FactoryTestDiMock::class], ['hello']);
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['hello'], $s1->args);

        // also OK if it's not a DIContainer object
        $s1 = Factory::factory([FactoryTestMock::class], ['hello']);
        $this->assertTrue($s1 instanceof FactoryTestMock);
        $this->assertSame(['hello'], $s1->args);
    }

    /**
     * Cannot inject in non-DI.
     */
    public function testNonDiInject()
    {
        $this->expectException(Exception::class);
        $s1 = Factory::factory([FactoryTestMock::class], ['foo' => 'hello']);
        $this->assertTrue($s1 instanceof FactoryTestDiMock);
        $this->assertSame(['hello'], $s1->args);
    }

    /**
     * Test seed property merging.
     */
    public function testPropertyMerging()
    {
        $s1 = Factory::factory(
            [FactoryTestDiMock::class, 'foo' => ['Button', 'icon' => 'red']],
            ['foo' => ['Label', 'red']]
        );

        $this->assertSame(['Button', 'icon' => 'red'], $s1->foo);

        $s1->setDefaults(['foo' => ['Message', 'detail' => 'blah']]);

        $this->assertSame(['Message', 'detail' => 'blah'], $s1->foo);
    }

    public function testFactory()
    {
        $m = new FactoryFactoryMock();

        // pass object
        $m1 = new FactoryFactoryMock();
        $m2 = Factory::factory($m1);
        $this->assertSame($m1, $m2);

        // from array seed
        $m1 = Factory::factory([FactoryFactoryMock::class]);
        $this->assertSame(FactoryFactoryMock::class, get_class($m1));

        // from string seed
        // $this->expectException(Exception::class);
        $this->expectDeprecation(); // replace with line above once support is removed (expected in 2020-dec)
        Factory::factory(FactoryFactoryMock::class);
    }

    /**
     * Object factory definition must use ["class name", "x"=>"y"] form.
     */
    public function testFactoryException1()
    {
        // wrong 1st parameter
        $this->expectException(Exception::class);
        $m = new FactoryFactoryMock();
        Factory::factory(['wrong_parameter' => 'qwerty']);
    }

    /**
     * Test factory parameters.
     */
    public function testFactoryParameters()
    {
        $m = new FactoryFactoryDiMock();

        // as class name
        $m1 = Factory::factory([FactoryFactoryMock::class]);
        $this->assertSame(FactoryFactoryMock::class, get_class($m1));

        $m2 = Factory::factory([HookBreaker::class], ['ok']);
        $this->assertSame(HookBreaker::class, get_class($m2));

        // as object
        $m2 = Factory::factory($m1);
        $this->assertSame(FactoryFactoryMock::class, get_class($m2));

        // as class name with parameters
        $m1 = Factory::factory([FactoryFactoryDiMock::class], ['a' => 'XXX', 'b' => 'YYY']);
        $this->assertSame('XXX', $m1->a);
        $this->assertSame('YYY', $m1->b);
        $this->assertNull($m1->c);

        $m1 = Factory::factory([FactoryFactoryDiMock::class], ['a' => null, 'b' => 'YYY', 'c' => 'ZZZ']);
        $this->assertSame('AAA', $m1->a);
        $this->assertSame('YYY', $m1->b);
        $this->assertSame('ZZZ', $m1->c);

        // as object with parameters
        $m1 = Factory::factory([FactoryFactoryDiMock::class]);
        $m2 = Factory::factory($m1, ['a' => 'XXX', 'b' => 'YYY']);
        $this->assertSame('XXX', $m2->a);
        $this->assertSame('YYY', $m2->b);
        $this->assertNull($m2->c);

        $m1 = Factory::factory([FactoryFactoryDiMock::class]);
        $m2 = Factory::factory($m1, ['a' => null, 'b' => 'YYY', 'c' => 'ZZZ']);
        $this->assertSame('AAA', $m2->a);
        $this->assertSame('YYY', $m2->b);
        $this->assertSame('ZZZ', $m2->c);

        $m1 = Factory::factory([FactoryFactoryDiMock::class], ['a' => null, 'b' => 'YYY', 'c' => 'SSS']);
        $m2 = Factory::factory($m1, ['a' => 'XXX', 'b' => null, 'c' => 'ZZZ']);
        $this->assertSame('XXX', $m2->a);
        $this->assertSame('YYY', $m2->b);
        $this->assertSame('ZZZ', $m2->c);

        // as object wrapped in array
        // $this->expectException(Exception::class);
        $this->expectDeprecation(); // replace with line above once support is removed (expected in 2020-dec)
        Factory::factory([$m1]);
    }

    /**
     * Object factory can not add not defined properties.
     * Receive as class name.
     */
    public function testFactoryParametersException1()
    {
        // wrong property in 2nd parameter
        $this->expectException(Exception::class);
        $m = new FactoryFactoryMock();
        $m1 = Factory::factory([FactoryFactoryMock::class], ['not_exist' => 'test']);
    }

    /**
     * Object factory can not add not defined properties.
     * Receive as object.
     */
    public function testFactoryParametersException2()
    {
        // wrong property in 2nd parameter
        $this->expectException(Exception::class);
        $m = new FactoryFactoryMock();
        $m1 = Factory::factory([FactoryFactoryMock::class]);
        $m2 = Factory::factory($m1, ['not_exist' => 'test']);
    }
}

class FactoryTestMock
{
    public $args;
    public $foo;
    public $baz = 0;

    public function __construct(...$args)
    {
        $this->args = $args;
    }
}

class FactoryTestDiMock extends FactoryTestMock
{
    use InjectableTrait;
}

class FactoryTestViewMock extends FactoryTestMock
{
    use InjectableTrait {
        setDefaults as _setDefaults;
    }
    public $def;

    public function setDefaults(array $properties, bool $passively = false)
    {
        if (array_key_exists('foo', $properties)) {
            if ($passively) {
                $this->foo = array_merge($properties['foo'], $this->foo);
            } else {
                $this->foo = array_merge($this->foo, $properties['foo']);
            }
            unset($properties['foo']);
        }

        return $this->_setDefaults($properties, $passively);
    }
}

class FactoryTestDefMock extends FactoryTestMock
{
    use InjectableTrait {
        setDefaults as _setDefaults;
    }
    public $def;

    public function setDefaults(array $properties, bool $passively = false)
    {
        $this->def = $properties;
    }
}

class FactoryFactoryMock
{
    public $a = 'AAA';
    public $b = 'BBB';
    public $c;
}

class FactoryFactoryDiMock
{
    use InjectableTrait;

    public $a = 'AAA';
    public $b = 'BBB';
    public $c;
}
