<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * This trait makes it possible for you to add dynamic methods
 * into your object.
 */
trait DynamicMethodTrait
{
    use HookTrait;

    /**
     * Magic method - tries to call dynamic method and throws exception if
     * this was not possible.
     *
     * @param string $methodName Name of the method
     * @param array  $args       Array of arguments to pass to this method
     */
    public function __call(string $methodName, $args)
    {
        if ($ret = $this->tryCall($methodName, $args)) {
            return reset($ret);
        }

        throw (new Exception('Method ' . $methodName . ' is not defined for this object'))
            ->addMoreInfo('class', static::class)
            ->addMoreInfo('method', $methodName)
            ->addMoreInfo('args', $args);
    }

    private function getMethodHookName(string $methodName, bool $global = false): string
    {
        return '__phlex__method__' . ($global ? 'g' : 'l') . '__' . $methodName;
    }

    /**
     * Tries to call dynamic method.
     *
     * @return mixed
     */
    public function tryCall(string $methodName, $args)
    {
        if (TraitUtil::hasHookTrait($this) && $ret = $this->hook($this->getMethodHookName($methodName), $args)) {
            return $ret;
        }

        if (TraitUtil::hasAppScopeTrait($this) && TraitUtil::hasHookTrait($this->getApp())) {
            array_unshift($args, $this);
            if ($ret = $this->getApp()->hook($this->getMethodHookName($methodName, true), $args)) {
                return $ret;
            }
        }

        return null;
    }

    /**
     * Add new method for this object.
     *
     * @return $this
     */
    public function addMethod(string $methodName, \Closure $fx)
    {
        if ($this->hasMethod($methodName)) {
            throw (new Exception('Registering method twice'))
                ->addMoreInfo('name', $methodName);
        }

        $this->onHook($this->getMethodHookName($methodName), $fx);

        return $this;
    }

    /**
     * Return if this object has specified method (either native or dynamic).
     */
    public function hasMethod(string $methodName): bool
    {
        return method_exists($this, $methodName)
            || (TraitUtil::hasHookTrait($this) && $this->hookHasCallbacks($this->getMethodHookName($methodName)));
    }

    /**
     * Remove dynamically registered method.
     */
    public function removeMethod(string $methodName)
    {
        if (TraitUtil::hasHookTrait($this)) {
            $this->removeHook($this->getMethodHookName($methodName));
        }

        return $this;
    }

    /**
     * Agile Toolkit objects allow method injection. This is quite similar
     * to technique used in JavaScript:.
     *
     *     obj.test = function() { .. }
     *
     * All non-existent method calls on all Agile Toolkit objects will be
     * tried against local table of registered methods and then against
     * global registered methods.
     *
     * addGlobalMethod allows you to register a globally-recognized method for
     * all Agile Toolkit objects. PHP is not particularly fast about executing
     * methods like that, but this technique can be used for adding
     * backward-compatibility or debugging, etc.
     *
     * @see self::hasMethod()
     * @see self::__call()
     *
     * @param string $name Name of the method
     */
    public function addGlobalMethod(string $name, \Closure $fx): void
    {
        // AppScopeTrait and HookTrait for app are mandatory
        if (!TraitUtil::hasAppScopeTrait($this) || !TraitUtil::hasHookTrait($this->getApp())) {
            throw new Exception('You need AppScopeTrait and HookTrait traits, see docs');
        }

        if ($this->hasGlobalMethod($name)) {
            throw (new Exception('Registering global method twice'))
                ->addMoreInfo('name', $name);
        }

        $this->getApp()->onHook($this->getMethodHookName($name, true), $fx);
    }

    /**
     * Return true if such global method exists.
     *
     * @param string $name Name of the method
     */
    public function hasGlobalMethod(string $name): bool
    {
        return TraitUtil::hasAppScopeTrait($this)
            && TraitUtil::hasHookTrait($this->getApp())
            && $this->getApp()->hookHasCallbacks($this->getMethodHookName($name, true));
    }

    /**
     * Remove dynamically registered global method.
     *
     * @param string $name Name of the method
     */
    public function removeGlobalMethod(string $name): void
    {
        if (TraitUtil::hasAppScopeTrait($this) && TraitUtil::hasHookTrait($this->getApp())) {
            $this->getApp()->removeHook($this->getMethodHookName($name, true));
        }
    }
}
