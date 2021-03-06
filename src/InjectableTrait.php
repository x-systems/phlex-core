<?php

declare(strict_types=1);

namespace Phlex\Core;

use Illuminate\Support\Str;

/**
 * A class with this trait will have setDefaults() method that can
 * be passed list of default properties.
 *
 * $view->setDefaults(['ui' => 'segment']);
 *
 * Typically you would want to do that inside your constructor. The
 * default handling of the properties is:
 *
 *  - only apply properties that are defined
 *  - only set property if it's current value is null
 *  - ignore defaults that have null value
 *  - if existing property and default have array, then both arrays will be merged
 *
 * Several classes may opt to extend setDefaults, for example in Agile UI
 * setDefaults is extended to support classes and content:
 *
 * $segment->setDefaults(['Hello There', 'red', 'ui'=>'segment']);
 *
 * WARNING: Do not use this trait unless you have a lot of properties
 * to inject. Also follow the guidelines on
 * https://github.com/atk4/ui/wiki/Object-Constructors
 *
 * Relying on this trait excessively may cause anger management issues to
 * some code reviewers.
 */
trait InjectableTrait
{
    /**
     * Call from __construct() to initialize the properties allowing
     * developer to pass Dependency Injector Container.
     *
     * @param bool $passively If true, existing non-null values will be kept
     *
     * @return $this
     */
    public function setDefaults(array $properties, bool $passively = false)
    {
        foreach ($properties as $name => $value) {
            $setterName = 'set' . Str::studly($name);
            $setterExists = method_exists($this, $setterName) && $setterName !== 'setDefaults';

            if (property_exists($this, $name) || $setterExists) {
                $origValue = $this->{$name} ?? null;

                if ($passively && $origValue !== null) {
                    continue;
                }

                if ($value !== null) {
                    if ($setterExists) {
                        $this->{$setterName}($value);
                    } else {
                        $this->{$name} = $value;
                    }
                }
            } else {
                $this->setMissingProperty($name, $value);
            }
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    protected function setMissingProperty(string $name, $value): void
    {
        throw (new Exception('Property for specified object is not defined'))
            ->addMoreInfo('object', $this)
            ->addMoreInfo('property', $name)
            ->addMoreInfo('value', $value);
    }

    /**
     * Return the argument and assert it is instance of current class.
     *
     * The best, typehinting-friendly, way to annotate object type if it not defined
     * at method header or strong typing in method header can not be used.
     *
     * @return static
     */
    public static function assertInstanceOf(object $object)// :static supported by PHP8+
    {
        if (!($object instanceof static)) {
            throw (new Exception('Object is not an instance of static class'))
                ->addMoreInfo('static_class', static::class)
                ->addMoreInfo('object_class', get_class($object));
        }

        return $object;
    }

    private static function _fromSeedPrecheck($seed, bool $unsafe)// :self is too strict with unsafe behaviour
    {
        if (!is_object($seed)) {
            if (!is_array($seed)) {
                throw (new Exception('Seed must be an array or an object'))
                    ->addMoreInfo('seed_type', gettype($seed));
            }

            if (!isset($seed[0])) {
                throw (new Exception('Class name is not specified by the seed'))
                    ->addMoreInfo('seed', $seed);
            }

            $cl = $seed[0];
            if (!$unsafe && !is_a($cl, static::class, true)) {
                throw (new Exception('Seed class is not a subtype of static class'))
                    ->addMoreInfo('static_class', static::class)
                    ->addMoreInfo('seed_class', $cl);
            }
        }

        return $seed;
    }

    /**
     * Create new object from seed and assert it is instance of current class.
     *
     * The best, typehinting-friendly, way to create an object if it should not be
     * immediately added to a parent (otherwise use addTo() method).
     *
     * @param array|object $seed the first element specifies a class name, other elements are seed
     *
     * @return static
     */
    public static function fromSeed($seed = [], $defaults = [])// :static supported by PHP8+
    {
        if (func_num_args() > 2) { // prevent bad usage
            throw new \Error('Too many method arguments');
        }

        $seed = self::_fromSeedPrecheck($seed, false);
        $object = Factory::factory($seed, $defaults);

        static::assertInstanceOf($object);

        return $object;
    }

    /**
     * Same as fromSeed(), but the new object is not asserted to be an instance of this class.
     *
     * @param array|object $seed the first element specifies a class name, other elements are seed
     *
     * @return static
     */
    public static function fromSeedUnsafe($seed = [], $defaults = [])// :self is too strict with unsafe behaviour
    {
        if (func_num_args() > 2) { // prevent bad usage
            throw new \Error('Too many method arguments');
        }

        $seed = self::_fromSeedPrecheck($seed, true);
        $object = Factory::factory($seed, $defaults);

        return $object;
    }
}
