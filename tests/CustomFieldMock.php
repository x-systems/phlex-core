<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\InitializerTrait;
use Phlex\Core\TrackableTrait;

class CustomFieldMock extends FieldMock
{
    use AppScopeTrait;
    use InitializerTrait {
        init as _init;
    }
    use TrackableTrait;

    /** @var null verifying if init wal called */
    public $var;

    protected function init(): void
    {
        $this->_init();

        $this->var = true;
    }
}
