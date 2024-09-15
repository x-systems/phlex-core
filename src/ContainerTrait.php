<?php

declare(strict_types=1);

namespace Phlex\Core;

/**
 * This trait makes it possible for you to add child objects
 * into your object.
 */
trait ContainerTrait
{
    /**
     * Element shortName => object hash of children objects. If the child is not
     * trackable, then object will be set to "true" (to avoid extra reference).
     *
     * @var array<string, object>
     */
    public array $elements = [];

    /** @var array<string, int> */
    private array $_elementNameCounts = [];

    /**
     * Returns unique element name based on desired name.
     */
    protected function _uniqueElementName(string $desired): string
    {
        if (!isset($this->_elementNameCounts[$desired])) {
            $this->_elementNameCounts[$desired] = 1;
            $postfix = '';
        } else {
            $postfix = '_' . (++$this->_elementNameCounts[$desired]);
        }

        return $desired . $postfix;
    }

    /**
     * If you are using ContainerTrait only, then you can safely
     * use this add() method. If you are also using factory, or
     * initializer then redefine add() and call _addContainer, _addFactory.
     *
     * @param object|array<mixed, mixed> $obj
     * @param array<mixed, mixed>|string $args
     */
    public function add($obj, $args = []): object
    {
        $obj = Factory::factory($obj, is_string($args) ? [] : array_diff_key($args, [true, 'desiredName' => true]));

        $this->_addContainer($obj, is_string($args) ? ['elementName' => $args] : $args);

        if (TraitUtil::hasInitializerTrait($obj)) {
            if (!$obj->isInitialized()) {
                try {
                    $obj->initialize();
                } catch (\Throwable $e) {
                    if (TraitUtil::hasTrackableTrait($obj)) {
                        unset($this->elements[$obj->elementId]);
                    }

                    throw $e;
                }
            }
        }

        return $obj;
    }

    /**
     * Extension to add() method which will perform linking of
     * the object with the current class.
     *
     * @param array{desiredName?: string, name?: string} $args
     */
    protected function _addContainer(object $element, array $args): void
    {
        // carry on reference to application if we have appScopeTraits set
        if (TraitUtil::hasAppScopeTrait($this) && TraitUtil::hasAppScopeTrait($element)
            && (!$element->issetApp() || $element->getApp() !== $this->getApp())
        ) {
            $element->setApp($this->getApp());
        }

        // if element is not trackable, then we don't need to do anything with it
        if (!TraitUtil::hasTrackableTrait($element)) {
            return;
        }

        // normalize the arguments, bring name out
        if (isset($args['desiredName'])) {
            $name = $this->_uniqueElementName($args['desiredName']);
            unset($args['desiredName']);
        } elseif (isset($args['elementName'])) {
            $name = $args['elementName'];
            unset($args['elementName']);
        } elseif (($element->elementId ?? null) !== null) {
            $name = $this->_uniqueElementName($element->elementId);
        } else {
            $desiredName = $element->getDesiredName();
            $name = $this->_uniqueElementName($desiredName);
        }

        if ($args !== []) {
            throw (new Exception('Add args for DI are no longer supported'))
                ->addMoreInfo('arg', $args);
        }

        // maybe element already exists
        if (isset($this->elements[$name])) {
            throw (new Exception('Element with requested name already exists'))
                ->addMoreInfo('element', $element)
                ->addMoreInfo('name', $name)
                ->addMoreInfo('this', $this)
                ->addMoreInfo('arg2', $args);
        }

        $element->setOwner($this);
        $element->elementId = $name;
        if (TraitUtil::hasTrackableTrait($this) && TraitUtil::hasNameTrait($this) && TraitUtil::hasNameTrait($element)) {
            $element->elementName = $this->_shorten($this->elementName ?? '', $element->elementId, $element->elementName ?? null); // @phpstan-ignore property.notFound
        }

        $this->elements[$element->elementId] = $element;
    }

    /**
     * Remove child element if it exists.
     *
     * @param string|object $elementId short name of the element
     *
     * @return $this
     */
    public function removeElement($elementId)
    {
        if (is_object($elementId)) {
            $elementId = $elementId->elementId;
        }

        if (!isset($this->elements[$elementId])) {
            throw (new Exception('Child element not found'))
                ->addMoreInfo('parent', $this)
                ->addMoreInfo('element', $elementId);
        }

        unset($this->elements[$elementId]);

        return $this;
    }

    /**
     * Method used internally for shortening object names.
     */
    protected function _shorten(string $ownerName, string $elementId, ?string $elementName): string
    {
        $desired = $elementName ?? $ownerName . '_' . $elementId;

        if (TraitUtil::hasAppScopeTrait($this)
            && isset($this->getApp()->maxNameLength)
            && mb_strlen($desired) > $this->getApp()->maxNameLength
        ) {
            if ($elementName !== null) {
                throw (new Exception('Element has too long desired name'))
                    ->addMoreInfo('name', $elementName);
            }

            $left = mb_strlen($desired) + 35 - $this->getApp()->maxNameLength;
            $key = mb_substr($desired, 0, $left);
            $rest = mb_substr($desired, $left);

            if (!isset($this->getApp()->uniqueNameHashes[$key])) {
                $this->getApp()->uniqueNameHashes[$key] = '_' . md5($key);
            }
            $desired = $this->getApp()->uniqueNameHashes[$key] . '__' . $rest;
        }

        return $desired;
    }

    /**
     * Find child element by its ID. Use in chaining.
     * Exception if not found.
     *
     * @param string $elementId ID of the child element
     */
    public function getElement(string $elementId): object
    {
        if (!isset($this->elements[$elementId])) {
            throw (new Exception('Child element not found'))
                ->addMoreInfo('parent', $this)
                ->addMoreInfo('element', $elementId);
        }

        return $this->elements[$elementId];
    }

    /**
     * @param string $elementId ID of the child element
     */
    public function hasElement($elementId): bool
    {
        return isset($this->elements[$elementId]);
    }
}
