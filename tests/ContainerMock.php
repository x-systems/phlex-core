<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\ContainerTrait;
use Phlex\Core\NameTrait;

class ContainerMock
{
    use ContainerTrait;
    use NameTrait;

    public function getElementCount(): int
    {
        return count($this->elements);
    }
}
