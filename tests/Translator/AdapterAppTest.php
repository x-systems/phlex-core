<?php

declare(strict_types=1);

namespace Phlex\Core\Tests\Translator;

use Phlex\Core\AppScopeTrait;
use Phlex\Core\TranslatableTrait;

class AdapterAppTest extends AdapterTestCase
{
    #[\Override]
    public function getTranslatableMock(): object
    {
        $app = new class() {
            use TranslatableTrait;
        };

        $mock = new class() {
            use AppScopeTrait;
            use TranslatableTrait;
        };

        $mock->setApp($app);

        return $mock;
    }
}
