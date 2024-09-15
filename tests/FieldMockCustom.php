<?php

declare(strict_types=1);

namespace Phlex\Core\Tests;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\InitializerTrait;
use Phlex\Core\NameTrait;
use Phlex\Core\TrackableTrait;

class FieldMockCustom extends FieldMock
{
    use AppScopeTrait;
    use InitializerTrait;
    use NameTrait;
    use TrackableTrait;

    /** @var bool verifying if init was called */
    public $var;

    protected function doInitialize(): void
    {
        $this->var = true;
    }
}
