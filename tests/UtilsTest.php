<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\PHPUnit\TestCase;
use Phlex\Core\Utils;

/**
 * @coversDefaultClass \Phlex\Core\ReadableCaptionTrait
 */
class UtilsTest extends TestCase
{
    /**
     * Test readableCaption method.
     */
    public function testReadableCaption(): void
    {
        self::assertSame('User Defined Entity', Utils::getReadableCaption('userDefinedEntity'));
        self::assertSame('New NASA Module', Utils::getReadableCaption('newNASA_module'));
        self::assertSame('This Is NASA My Big Bull Shit 123 Foo', Utils::getReadableCaption('this\ _isNASA_MyBigBull shit_123\Foo'));

        self::assertSame('ID', Utils::getReadableCaption('id'));
        self::assertSame('Account ID', Utils::getReadableCaption('account_id'));
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

        self::assertSame('datetime', Utils::resolveFromRegistry($registry, \DateTime::class));
        self::assertSame('default', Utils::resolveFromRegistry($registry, 'nonexistent'));
        self::assertSame('exception', Utils::resolveFromRegistry($registry, \ErrorException::class));
        self::assertSame('typeerror', Utils::resolveFromRegistry($registry, \TypeError::class));
    }
}
