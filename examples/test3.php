<?php

declare(strict_types=1);

require '../vendor/autoload.php';

class MyClass2
{
    use \Phlex\Core\DynamicMethodTrait;
    use \Phlex\Core\HookTrait;
}

$c = new MyClass2();

$c->addMethod('mymethod', function ($c, $a, $b) {
    return $a + $b;
});

// @phpstan-ignore-next-line
echo $c->mymethod(2, 3) . "\n";
