<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * This trait makes it possible for you to add child objects
 * into your object, but unlike "ContainerTrait" you can use
 * multiple collections stored as different array properties.
 *
 * This class does not offer automatic naming, so if you try
 * to add another element with same name, it will result in
 * exception.
 */
trait CollectionTrait
{
    /**
     * Use this method trait like this:.
     *
     * function addField($name, $definition) {
     *     $field = Field::fromSeed($seed);
     *
     *     return $this->_addIntoCollection($name, $field, 'fields');
     * }
     *
     * @param string $collection property name
     */
    public function _addIntoCollection(string $elementId, object $item, string $collection): object
    {
        if (!isset($this->{$collection}) || !is_array($this->{$collection})) {
            throw (new Exception('Collection does NOT exist'))
                ->addMoreInfo('collection', $collection);
        }

        if ($elementId === '') {
            throw (new Exception('Empty name is not supported'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('element', $elementId);
        }

        if ($this->_hasInCollection($elementId, $collection)) {
            throw (new Exception('Element with the same name already exist in the collection'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('element', $elementId);
        }
        $this->{$collection}[$elementId] = $item;

        // Carry on reference to application if we have appScopeTraits set
        if (isset($this->_appScopeTrait) && isset($item->_appScopeTrait)) {
            $item->setApp($this->getApp());
        }

        // Calculate long "name" but only if both are trackables
        if (isset($item->_trackableTrait)) {
            $item->elementId = $elementId;
            $item->setOwner($this);
            if (isset($this->_trackableTrait)) {
                $item->elementName = $this->_shorten_ml($this->elementName . '-' . $collection . '_' . $elementId);
            }
        }

        if (isset($item->_initializerTrait)) {
            if (!$item->isInitialized()) {
                $item->initialize();
            }
        }

        return $item;
    }

    /**
     * Removes element from specified collection.
     *
     * @param string $collection property name
     */
    public function _removeFromCollection(string $elementId, string $collection): void
    {
        if (!$this->_hasInCollection($elementId, $collection)) {
            throw (new Exception('Element is NOT in the collection'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('element', $elementId);
        }
        unset($this->{$collection}[$elementId]);
    }

    /**
     * Call this on collections after cloning object. This will clone all collection
     * elements (which are objects).
     *
     * @param string $collectionName property name to be cloned
     */
    public function _cloneCollection(string $collectionName): void
    {
        $this->{$collectionName} = array_map(function ($item) {
            $item = clone $item;
            if (isset($item->_trackableTrait) && $item->issetOwner()) {
                $item->unsetOwner()->setOwner($this);
            }

            return $item;
        }, $this->{$collectionName});
    }

    /**
     * Returns true if and only if collection exists and object with given name is presented in it.
     *
     * @param string $collection property name
     */
    public function _hasInCollection(string $elementId, string $collection): bool
    {
        $data = $this->{$collection};

        return isset($data[$elementId]);
    }

    /**
     * @param string $collection property name
     */
    public function _getFromCollection(string $elementId, string $collection): object
    {
        if (!$this->_hasInCollection($elementId, $collection)) {
            throw (new Exception('Element is NOT in the collection'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('name', $elementId);
        }

        return $this->{$collection}[$elementId];
    }

    /**
     * Method used internally for shortening object names
     * Identical implementation to ContainerTrait::_shorten.
     *
     * @param string $desired desired name of the object
     *
     * @return string shortened name
     */
    protected function _shorten_ml(string $desired): string
    {
        // ugly hack to deduplicate code
        $collectionTraitHelper = \Closure::bind(function () {
            $factory = Factory::getInstance();
            if (!property_exists($factory, 'collectionTraitHelper')) {
                // @phpstan-ignore-next-line
                $factory->collectionTraitHelper = new class() {
                    use AppScopeTrait;
                    use ContainerTrait;

                    public function shorten(?object $app, string $desired): string
                    {
                        $this->_appScopeTrait = $app !== null;

                        try {
                            $this->setApp($app);

                            return $this->_shorten($desired);
                        } finally {
                            $this->_app = null; // important for GC
                        }
                    }
                };
            }

            // @phpstan-ignore-next-line
            return $factory->collectionTraitHelper;
        }, null, Factory::class)();

        return $collectionTraitHelper->shorten($this->_appScopeTrait ? $this->getApp() : null, $desired);
    }
}
