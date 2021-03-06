<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\NameTrait;
use Phlex\Core\SessionTrait;

/**
 * @coversDefaultClass \Phlex\Core\SessionTrait
 */
class SessionTraitTest extends \Phlex\Core\PHPUnit\TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        session_abort();
        $sessionDir = sys_get_temp_dir() . '/atk4_test__ui__session';
        if (!file_exists($sessionDir)) {
            mkdir($sessionDir);
        }
        ini_set('session.save_path', $sessionDir);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        session_abort();
        $sessionDir = ini_get('session.save_path');
        foreach (scandir($sessionDir) as $f) {
            if (!in_array($f, ['.', '..'], true)) {
                unlink($sessionDir . '/' . $f);
            }
        }

        rmdir($sessionDir);
    }

    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $m = new SessionMock();

        $this->assertFalse(isset($_SESSION));
        $m->startSession();
        $this->assertTrue(isset($_SESSION));
        $m->destroySession();
        $this->assertFalse(isset($_SESSION));
    }

    /**
     * Test memorize().
     */
    public function testMemorize()
    {
        $m = new SessionMock();
        $m->elementName = 'test';

        // value as string
        $m->memorize('foo', 'bar');
        $this->assertSame('bar', $_SESSION['__phlex_session'][$m->elementName]['foo']);

        // value as null
        $m->memorize('foo', null);
        $this->assertNull($_SESSION['__phlex_session'][$m->elementName]['foo']);

        // value as object
        $o = new \StdClass();
        $m->memorize('foo', $o);
        $this->assertSame($o, $_SESSION['__phlex_session'][$m->elementName]['foo']);

        $m->destroySession();
    }

    /**
     * Test learn(), recall(), forget().
     */
    public function testLearnRecallForget()
    {
        $m = new SessionMock();
        $m->elementName = 'test';

        // value as string
        $m->learn('foo', 'bar');
        $this->assertSame('bar', $m->recall('foo'));

        $m->learn('foo', 'qwerty');
        $this->assertSame('bar', $m->recall('foo'));

        $m->forget('foo');
        $this->assertSame('undefined', $m->recall('foo', 'undefined'));

        // value as callback
        $m->learn('foo', fn ($key) => $key . '_bar');
        $this->assertSame('foo_bar', $m->recall('foo'));

        $m->learn('foo_2', 'another');
        $this->assertSame('another', $m->recall('foo_2'));

        $v = $m->recall('foo_3', fn ($key) => $key . '_bar');
        $this->assertSame('foo_3_bar', $v);
        $this->assertSame('undefined', $m->recall('foo_3', 'undefined'));

        $m->forget();
        $this->assertSame('undefined', $m->recall('foo', 'undefined'));
        $this->assertSame('undefined', $m->recall('foo_2', 'undefined'));
        $this->assertSame('undefined', $m->recall('foo_3', 'undefined'));
    }
}

// @codingStandardsIgnoreStart
class SessionMock
{
    use NameTrait;
    use SessionTrait;
}
// @codingStandardsIgnoreEnd
