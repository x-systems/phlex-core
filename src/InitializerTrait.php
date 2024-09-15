<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * Object with this trait will have it's doInitialize() method executed
 * automatically when initialized through add().
 */
trait InitializerTrait
{
    private bool $_initialized = false;

    public function initialize(): void
    {
        // assert initialize() method is not declared as public, ie. not easily directly callable by the user
        if ((new \ReflectionMethod($this, 'doInitialize'))->getModifiers() & \ReflectionMethod::IS_PUBLIC) {
            throw new Exception('doInitialize method must have protected visibility');
        }

        if ($this->isInitialized()) {
            throw (new Exception('Object already initialized'))
                ->addMoreInfo('this', $this);
        }
        $this->_initialized = true;

        $this->doInitialize();

        $this->assertIsInitialized();
    }

    /**
     * Perform object specific initialization. Always call parent::doInitialize(). Do not call directly.
     *
     * #[\Override]
     */
    protected function doInitialize(): void {}

    public function isInitialized(): bool
    {
        return $this->_initialized;
    }

    public function assertIsInitialized(): void
    {
        if (!$this->isInitialized()) {
            throw new Exception('Object was not initialized');
        }
    }
}
