<?php

declare(strict_types=1);

namespace Phlex\Core\Hintable;

trait PropTrait
{
    /**
     * Returns a magic class that pretends to be instance of this class, but in reality
     * any property returns its (short) name.
     *
     * @return static
     *
     * @phpstan-return MagicProp<static, string>
     */
    public function propName()
    {
        return Prop::propName($this); // @phpstan-ignore-line
    }

    /**
     * Returns a magic class that pretends to be instance of this class, but in reality
     * any property returns its full name, ie. class name + "::" + short name.
     *
     * @return static
     *
     * @phpstan-return MagicProp<static, string>
     */
    public function propNameFull()
    {
        return Prop::propNameFull($this); // @phpstan-ignore-line
    }
}
