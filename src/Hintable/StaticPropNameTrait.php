<?php

declare(strict_types=1);

namespace Phlex\Core\Hintable;

trait StaticPropNameTrait
{
    /**
     * Returns a magic class that pretends to be instance of this class, but in reality
     * any property returns its (short) name.
     *
     * @return static
     *
     * @phpstan-return MagicProp<static, string>
     */
    public static function propName()
    {
        return Prop::propName(static::class); // @phpstan-ignore-line
    }
}
