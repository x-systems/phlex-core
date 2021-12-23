<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\Utils;

/**
 * @coversDefaultClass \Phlex\Core\ReadableCaptionTrait
 */
class UtilsTest extends \Phlex\Core\PHPUnit\TestCase
{
    /**
     * Test readableCaption method.
     */
    public function testReadableCaption()
    {
        $this->assertSame('User Defined Entity', Utils::getReadableCaption('userDefinedEntity'));
        $this->assertSame('New NASA Module', Utils::getReadableCaption('newNASA_module'));
        $this->assertSame('This Is NASA My Big Bull Shit 123 Foo', Utils::getReadableCaption('this\\ _isNASA_MyBigBull shit_123\Foo'));
    }

    public function testResolveFromRegistry()
    {
        $registry = [
            'default',
            \DateTime::class => 'datetime',
            \Exception::class => 'exception',
            \Error::class => 'error',
            \TypeError::class => 'typeerror',
        ];

        $this->assertSame('datetime', Utils::resolveFromRegistry($registry, \DateTime::class));
        $this->assertSame('default', Utils::resolveFromRegistry($registry, 'nonexistent'));
        $this->assertSame('exception', Utils::resolveFromRegistry($registry, \ErrorException::class));
        $this->assertSame('typeerror', Utils::resolveFromRegistry($registry, \TypeError::class));
    }
}
