<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core;

class ContainerMock
{
    use core\ContainerTrait;
    use core\NameTrait;

    public function getElementCount()
    {
        return count($this->elements);
    }
}
