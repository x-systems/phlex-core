<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * Object with this trait will have it's init() method executed
 * automatically when initialized through add().
 */
trait InitializerTrait
{
    /**
     * Check this property to see if trait is present in the object.
     *
     * @var bool
     */
    public $_initializerTrait = true;

    /**
     * To make sure you have called parent::init() properly.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Initialize object.
     */
    public function initialize(): void
    {
        // assert doInitialize() method is not declared as public, ie. not easily directly callable by the user
        if ((new \ReflectionMethod($this, 'doInitialize'))->getModifiers() & \ReflectionMethod::IS_PUBLIC) {
            throw new Exception('doInitialize method must have protected visibility');
        }

        if ($this->initialized) {
            throw (new Exception('Attempting to initialize twice'))
                ->addMoreInfo('this', $this);
        }

        $this->initialized = true;

        $this->doInitialize();
    }

    protected function doInitialize(): void
    {
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}
