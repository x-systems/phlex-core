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
     * Check this property to see if trait is present in the object.
     *
     * @var bool
     */
    public $_containerTrait = true;

    /**
     * elementId => object hash of children objects. If the child is not
     * trackable, then object will be set to "true" (to avoid extra reference).
     *
     * @var array
     */
    public $elements = [];

    /**
     * @var int[]
     */
    private $elementNameCounts = [];

    /**
     * Returns unique element name based on desired name.
     */
    public function getUniqueElementName(string $desiredName): string
    {
        if (!isset($this->elementNameCounts[$desiredName])) {
            $this->elementNameCounts[$desiredName] = 1;
            $postfix = '';
        } else {
            $postfix = '_' . (++$this->elementNameCounts[$desiredName]);
        }

        return $desiredName . $postfix;
    }

    /**
     * If you are using ContainerTrait only, then you can safely
     * use this add() method. If you are also using factory, or
     * initializer then redefine add() and call
     * _add_Container, _add_Factory,.
     *
     * @param mixed        $obj
     * @param array|string $args
     */
    public function add($obj, $args = []): object
    {
        if (is_array($args)) {
            $args1 = $args;
            unset($args1['desiredName']);
            unset($args1[0]);
            $obj = Factory::factory($obj, $args1);
        } else {
            $obj = Factory::factory($obj);
        }
        $obj = $this->_add_Container($obj, $args);

        if (isset($obj->_initializerTrait)) {
            if (!$obj->isInitialized()) {
                $obj->initialize();
            }
        }

        return $obj;
    }

    /**
     * Extension to add() method which will perform linking of
     * the object with the current class.
     *
     * @param array|string $args
     */
    protected function _add_Container(object $element, $args = []): object
    {
        // Carry on reference to application if we have appScopeTraits set
        if (isset($this->_appScopeTrait) && isset($element->_appScopeTrait)) {
            $element->setApp($this->getApp());
        }

        // If element is not trackable, then we don't need to do anything with it
        if (!isset($element->_trackableTrait)) {
            return $element;
        }

        // Normalize the arguments, bring name out
        if (is_string($args)) {
            // passed as string
            $args = [$args];
        } elseif (!is_array($args) && $args !== null) {
            throw (new Exception('Second argument must be array'))
                ->addMoreInfo('arg2', $args);
        } elseif (isset($args['desiredName'])) {
            // passed as ['desiredName'=>'foo'];
            $args[0] = $this->getUniqueElementName($args['desiredName']);
            unset($args['desiredName']);
        } elseif (isset($args['elementName'])) {
            // passed as ['name'=>'foo'];
            $args[0] = $args['elementName'];
            unset($args['elementName']);
        } elseif (isset($element->elementId)) {
            // element has an id already
            $args[0] = $this->getUniqueElementName($element->elementId);
        } else {
            // ask element on his preferred name, then make it unique.
            $args[0] = $this->getUniqueElementName($element->getDesiredName());
        }

        // Maybe element already exists
        if (isset($this->elements[$args[0]])) {
            throw (new Exception('Element with requested name already exists'))
                ->addMoreInfo('element', $element)
                ->addMoreInfo('name', $args[0])
                ->addMoreInfo('this', $this)
                ->addMoreInfo('arg2', $args);
        }

        $element->setOwner($this);
        $element->elementId = $args[0];
        if (isset($this->_nameTrait)) {
            $element->elementName = $this->_shorten($this->elementName . '_' . $element->elementId);
        }
        $this->elements[$element->elementId] = $element;

        unset($args[0]);
        unset($args['name']);
        foreach ($args as $key => $arg) {
            if ($arg !== null) {
                $element->{$key} = $arg;
            }
        }

        return $element;
    }

    /**
     * Remove child element if it exists.
     *
     * @param string|object $elementId ID of the element
     */
    public function removeElement($elementId)
    {
        if (is_object($elementId)) {
            $elementId = $elementId->elementId;
        }

        if (!isset($this->elements[$elementId])) {
            throw (new Exception('Could not remove child from parent. Instead of destroy() try using removeField / removeColumn / ..'))
                ->addMoreInfo('parent', $this)
                ->addMoreInfo('element', $elementId);
        }

        unset($this->elements[$elementId]);

        return $this;
    }

    /**
     * Method used internally for shortening object names.
     *
     * @param string $desiredName desired name of new object
     *
     * @return string shortened name of new object
     */
    protected function _shorten(string $desiredName): string
    {
        if (isset($this->_appScopeTrait)
            && isset($this->getApp()->max_name_length)
            && mb_strlen($desiredName) > $this->getApp()->max_name_length) {
            /*
             * Basic rules: hash is 10 character long (8+2 for separator)
             * We need at least 5 characters on the right side. Total must not exceed
             * max_name_length. First chop will be max-10, then chop size will increase by
             * max-15
             */
            $len = mb_strlen($desiredName);
            $left = $len - ($len - 10) % ($this->getApp()->max_name_length - 15) - 5;

            $key = mb_substr($desiredName, 0, $left);
            $rest = mb_substr($desiredName, $left);

            if (!isset($this->getApp()->unique_hashes[$key])) {
                $this->getApp()->unique_hashes[$key] = '_' . dechex(crc32($key));
            }
            $desiredName = $this->getApp()->unique_hashes[$key] . '__' . $rest;
        }

        return $desiredName;
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
