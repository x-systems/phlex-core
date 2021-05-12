<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\InitializerTrait;
use Phlex\Core\TrackableTrait;

class CustomFieldMock extends FieldMock
{
    use AppScopeTrait;
    use InitializerTrait;
    use TrackableTrait;

    /** @var null verifying if init wal called */
    public $var;

    protected function doInitialize(): void
    {
        $this->var = true;
    }
}
