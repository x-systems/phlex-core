<?php

declare(strict_types=1);

namespace Phlex\Core\Tests\Hintable;

use Phlex\Core\Hintable\PropTrait;

class PropMock
{
    use PropTrait;

    /** @var string */
    public $pub = '_pub_';
    /** @var string */
    private $priv = '_priv_';
    /** @var int */
    public $pubInt = 21;
}
