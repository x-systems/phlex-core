<?php

declare(strict_types=1);

namespace Phlex\Core\Hintable\Phpstan;

use PHPStan\Reflection\Annotations\AnnotationPropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

class WrapPropertyReflection extends AnnotationPropertyReflection
{
    public function __construct(ClassReflection $declaringClass, Type $type)
    {
        parent::__construct($declaringClass, $type, true, false);
    }
}
