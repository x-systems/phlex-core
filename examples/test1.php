<?php

declare(strict_types=1);

require '../vendor/autoload.php';

class MyParentObject
{
    use \Phlex\Core\ContainerTrait;
}

class MyChildClass
{
    use \Phlex\Core\TrackableTrait;
}

$parent = new MyParentObject();

$parent->add(new MyChildClass(), 'foo-bar');

var_dump($parent->getElement('foo-bar'));
