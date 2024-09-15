<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\ContainerTrait;
use Phlex\Core\Exception;
use Phlex\Core\NameTrait;
use Phlex\Core\Phpunit\TestCase;
use Phlex\Core\TrackableTrait;

class AppScopeTraitTest extends TestCase
{
    public function testConstruct(): void
    {
        $m = new AppScopeMock();
        $fakeApp = new \stdClass();
        $m->setApp($fakeApp);

        $c = $m->add(new AppScopeChildBasic());
        self::assertSame($fakeApp, $c->getApp());

        $c = $m->add(new AppScopeChildWithoutAppScope());
        self::assertFalse(property_exists($c, 'app'));
        self::assertFalse(property_exists($c, '_app'));

        $m = new AppScopeMock2();

        $c = $m->add(new AppScopeChildBasic());
        self::assertFalse($c->issetApp());

        // test for GC
        $m = new AppScopeMock();
        $m->setApp($m);
        $child = new AppScopeChildTrackable();
        $m->add($child);
        $child->destroy();
        self::assertNull(\Closure::bind(static fn () => $child->_app, null, AppScopeChildTrackable::class)());
        self::assertFalse($child->issetOwner());
    }

    public function testAppNotSetException(): void
    {
        $m = new AppScopeMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Webpage is not set');
        $m->getApp();
    }

    public function testAppSetTwiceException(): void
    {
        $m = new AppScopeMock();
        $fakeApp = new \stdClass();
        $m->setApp($fakeApp);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Webpage is already set');
        $m->setApp($fakeApp);
    }
}

class AppScopeMock
{
    use AppScopeTrait;
    use ContainerTrait;
    use NameTrait;

    /**
     * @param array{desiredName?: string, name?: string} $args
     */
    public function add(object $obj, array $args = []): object
    {
        $this->_addContainer($obj, $args);

        return $obj;
    }
}

class AppScopeMock2
{
    use ContainerTrait;

    /**
     * @param array{desiredName?: string, name?: string} $args
     */
    public function add(object $obj, array $args = []): object
    {
        $this->_addContainer($obj, $args);

        return $obj;
    }
}

class AppScopeChildBasic
{
    use AppScopeTrait;
}

class AppScopeChildWithoutAppScope
{
    use TrackableTrait;
}

class AppScopeChildTrackable
{
    use AppScopeTrait;
    use TrackableTrait;
}
