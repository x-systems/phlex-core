<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * Provides method for merging parent registry property values.
 */
trait InheritableRegistryTrait
{
    /**
     * Merges array of options from parent classes.
     *
     * @return $this
     */
    protected function inheritRegistry($property)
    {
        $class = static::class;

        if (!property_exists($class, $property)) {
            (new Exception('Property for specified object is not defined'))
                ->addMoreInfo('class', $class)
                ->addMoreInfo('property', $property);
        }

        while ($class = get_parent_class($class)) {
            $this->{$property} += get_class_vars($class)[$property] ?? [];
        }

        return $this;
    }
}
