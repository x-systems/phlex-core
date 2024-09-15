<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\Exception;
use Phlex\Core\Phpunit\TestCase;
use Phlex\Core\QuietObjectWrapper;

class QuietObjectWrapperTest extends TestCase
{
    public function testBasic(): void
    {
        $oOrig = new \stdClass();
        $o = new QuietObjectWrapper($oOrig);
        self::assertSame($oOrig, $o->get());
    }

    public function testNotCloneable(): void
    {
        $o = new QuietObjectWrapper(new \stdClass());

        $this->expectException(\Error::class);
        clone $o; // @phpstan-ignore expr.resultUnused
    }

    public function testNotSerializeable(): void
    {
        $o = new QuietObjectWrapper(new \stdClass());

        $this->expectException(Exception::class);
        serialize($o);
    }

    public function testDebugInfoQuiet(): void
    {
        $o = new QuietObjectWrapper(new \stdClass());
        self::assertSame(<<<'EOF'
            Phlex\Core\QuietObjectWrapper Object
            (
                [wrappedClass] => stdClass
            )

            EOF, print_r($o, true));

        $o = new QuietObjectWrapper(new class() {
            /**
             * @return array<string, mixed>
             */
            public function __debugInfoQuiet(): array
            {
                return ['foo' => 1, 'Bar' => 2];
            }
        });
        self::assertSame(<<<'EOF'
            Phlex\Core\QuietObjectWrapper Object
            (
                [wrappedClass] => class@anonymous
                [foo] => 1
                [Bar] => 2
            )

            EOF, print_r($o, true));
    }
}
