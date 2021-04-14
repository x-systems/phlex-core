<?php

declare(strict_types=1);

namespace Phlex\Core\Tests\Hintable;

use Phlex\Hintable\MethodTrait;

class MethodMock
{
    use MethodTrait;

    private function priv(): string
    {
        return __METHOD__;
    }

    public function pub(): string
    {
        return __METHOD__;
    }

    private static function privStat(): string
    {
        return __METHOD__;
    }

    public static function pubStat(): string
    {
        return __METHOD__;
    }
}
