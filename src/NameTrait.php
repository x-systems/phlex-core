<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * This trait makes it possible to set name of your object.
 */
trait NameTrait
{
    /** @var non-falsy-string Unique object name. */
    public string $elementName;
}
