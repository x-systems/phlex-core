<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * Provides native methods for manipulating seeds from a registry property.
 */
trait SeedRegistryTrait
{
    /**
     * Holds the seed resolution registry.
     *
     * @var array
     */
//     protected $seeds = [];

    /**
     * Retrieves an option from the array.
     *
     * @return mixed
     */
    public function getSeed(string $class, $defaults = [])
    {
        return array_merge(Utils::resolveFromRegistry($this->seeds, $class), $defaults);
    }

    /**
     * Ensure additional seeds are merged with default ones.
     */
    public function setSeeds(array $seeds)
    {
        $this->seeds = $seeds + $this->seeds;

        return $this;
    }
}
