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
     * function addField(string $name, $definition)
     * {
     *     $field = Field::fromSeed($seed);
     *
     *     $this->_addIntoCollection($name, $field, 'fields');
     *
     *     return $field;
     * }
     *
     * @param string $collection property name
     */
    protected function _addIntoCollection(string $elementId, object $item, string $collection): void
    {
        if (!isset($this->{$collection}) || !is_array($this->{$collection})) {
            throw (new Exception('Collection does not exist'))
                ->addMoreInfo('collection', $collection);
        }

        if ($elementId === '') {
            throw (new Exception('Empty name is not supported'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('element', $elementId);
        }

        if ($this->_hasInCollection($elementId, $collection)) {
            throw (new Exception('Element with the same name already exists in the collection'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('element', $elementId);
        }

        // carry on reference to application if we have appScopeTraits set
        if ((TraitUtil::hasAppScopeTrait($this) && TraitUtil::hasAppScopeTrait($item))
            && (!$item->issetApp() || $item->getApp() !== $this->getApp())
        ) {
            $item->setApp($this->getApp());
        }

        // calculate long "name" but only if both are trackables
        if (TraitUtil::hasTrackableTrait($item)) {
            $item->elementId = $elementId;
            $item->setOwner($this);
            if (TraitUtil::hasTrackableTrait($this) && TraitUtil::hasNameTrait($this) && TraitUtil::hasNameTrait($item)) {
                $item->elementName = $this->_shortenMl($this->elementName ?? '', $collection, $item->elementId, $item->elementName ?? null); // @phpstan-ignore property.notFound
            }
        }

        $this->{$collection}[$elementId] = $item;

        if (TraitUtil::hasInitializerTrait($item)) {
            if (!$item->isInitialized()) {
                try {
                    $item->initialize();
                } catch (\Throwable $e) {
                    unset($this->{$collection}[$elementId]);

                    throw $e;
                }
            }
        }
    }

    /**
     * Removes element from specified collection.
     *
     * @param string $collection property name
     */
    protected function _removeFromCollection(string $elementId, string $collection): void
    {
        if (!$this->_hasInCollection($elementId, $collection)) {
            throw (new Exception('Element is not in the collection'))
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
    protected function _cloneCollection(string $collectionName): void
    {
        $this->{$collectionName} = array_map(function ($item) {
            $item = clone $item;
            if (TraitUtil::hasTrackableTrait($item) && $item->issetOwner()) {
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
    protected function _hasInCollection(string $elementId, string $collection): bool
    {
        return isset($this->{$collection}[$elementId]);
    }

    /**
     * @param string $collection property name
     */
    protected function _getFromCollection(string $elementId, string $collection): object
    {
        $res = $this->{$collection}[$elementId] ?? null;
        if ($res === null) {
            throw (new Exception('Element is not in the collection'))
                ->addMoreInfo('collection', $collection)
                ->addMoreInfo('name', $elementId);
        }

        return $res;
    }

    /**
     * Method used internally for shortening object names.
     *
     * Identical implementation to ContainerTrait::_shorten.
     */
    protected function _shortenMl(string $ownerName, string $collectionName, string $elementId, ?string $elementName): string
    {
        $ownerName .= '-' . $collectionName;

        if (TraitUtil::hasContainerTrait($this)) {
            return $this->_shorten($ownerName, $elementId, $elementName); // @phpstan-ignore method.notFound
        }

        // ugly hack to deduplicate code
        $collectionTraitHelper = new class() {
            use AppScopeTrait;
            use ContainerTrait;

            public function shorten(?object $app, string $ownerName, string $elementId, ?string $elementName): string
            {
                try {
                    $this->setApp($app);

                    return $this->_shorten($ownerName, $elementId, $elementName);
                } finally {
                    $this->_app = null; // important for GC
                }
            }
        };

        return $collectionTraitHelper->shorten(TraitUtil::hasAppScopeTrait($this) ? $this->getApp() : null, $ownerName, $elementId, $elementName);
    }
}
