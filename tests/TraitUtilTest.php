<?php

declare(strict_types=1);

namespace Phlex\Core\tests;

use Phlex\Core\Exception;
use Phlex\Core\HookTrait;
use Phlex\Core\NameTrait;
use Phlex\Core\Phpunit\TestCase;
use Phlex\Core\TraitUtil;
use PHPUnit\Framework\MockObject\Method as MockObjectMethodTrait;

class TraitUtilTest extends TestCase
{
    public function testHasTrait(): void
    {
        self::assertFalse(TraitUtil::hasTrait(TraitUtilTestA::class, NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(TraitUtilTestB::class, NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(TraitUtilTestC::class, NameTrait::class));

        self::assertFalse(TraitUtil::hasTrait(new TraitUtilTestA(), NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(new TraitUtilTestB(), NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(new TraitUtilTestC(), NameTrait::class));

        self::assertFalse(TraitUtil::hasTrait(new class() extends TraitUtilTestA {}, NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(new class() extends TraitUtilTestB {}, NameTrait::class));
        self::assertTrue(TraitUtil::hasTrait(new class() extends TraitUtilTestC {}, NameTrait::class));

        self::assertFalse(TraitUtil::hasTrait(TraitUtilTestA::class, HookTrait::class));
        self::assertTrue(TraitUtil::hasTrait(TraitUtilTestB::class, HookTrait::class));
        self::assertTrue(TraitUtil::hasTrait(TraitUtilTestC::class, HookTrait::class));
    }

    public function testHasTraitNoPhlexCoreException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(TraitUtil::class . '::hasTrait() method is not intended for use with other than Phlex\Core\* traits');
        TraitUtil::hasTrait(TraitUtilTestA::class, MockObjectMethodTrait::class);
    }
}

class TraitUtilTestA {}

trait TraitUtilTestTrait
{
    use HookTrait;
}

class TraitUtilTestB extends TraitUtilTestA
{
    use NameTrait;
    use TraitUtilTestTrait;
}

class TraitUtilTestC extends TraitUtilTestB {}
