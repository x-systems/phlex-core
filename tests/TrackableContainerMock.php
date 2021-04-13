<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core;

class TrackableContainerMock
{
    use core\ContainerTrait;
    use core\TrackableTrait;

    public function getElementCount()
    {
        return count($this->elements);
    }
}
