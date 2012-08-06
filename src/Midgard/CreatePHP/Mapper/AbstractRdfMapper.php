<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;
use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\RdfElementDefinitionInterface;

/**
 * Base mapper class with utility methods for generic operations.
 *
 * Extend and overwrite for your own mapper.
 *
 * @package Midgard.CreatePHP
 */
abstract class AbstractRdfMapper implements RdfMapperInterface
{
    /**
     * @var array of rdf type => class name
     */
    protected $typeMap;

    public function __construct($typeMap)
    {
        $this->typeMap = $typeMap;
    }

    /**
     * {@inheritDoc}
     */
    function prepareObject(TypeInterface $type, $parent = null)
    {
        if ($parent !== null) {
            throw new \Exception('Parent is not null, please extend this method to configure the parent');
        }
        if (isset($this->typeMap[$type->getRdfType()])) {
            $class = $this->typeMap[$type->getRdfType()];
            return new $class;
        }
        throw new \Exception('No information on '.$type->getRdfType());
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyValue($object, PropertyInterface $property, $value)
    {
        $class = new \ReflectionClass($object);

        $name = $property->getIdentifier();
        $method = 'set' . ucfirst($name);
        if ($class->hasMethod($method)) {
            $object->$method($value);
        } elseif ($class->hasProperty($name)) {
            $object->$name = $value;
        } else {
            throw new \Exception('Unknown property '.$property->getIdentifier());
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyValue($object, PropertyInterface $property)
    {
        return $this->getField($object, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function isEditable($object)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren($object, CollectionInterface $collection)
    {
        return $this->getField($object, $collection);
    }

    /**
     * Get a field of the class if it exists.
     *
     * @param mixed $object the model object
     * @param Type\RdfElementDefinitionInterface $child the property or collection
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function getField($object, RdfElementDefinitionInterface $child)
    {
        $class = new \ReflectionClass($object);

        $name = $child->getIdentifier();
        $method = 'get' . ucfirst($name);
        if ($class->hasMethod($method)) {
            return $object->$method();
        }
        if ($class->hasProperty($name)) {
            return $object->$name;
        }
        if (is_array($object) && array_key_exists($name, $object)) {
            return $object[$name];
        }

        throw new \Exception('Unknown field ' . $child->getIdentifier());
    }

    /**
     * A dummy classname canonicalizer: returns the name unmodified.
     *
     * @param string $className
     * @return string exactly the same as $className
     */
    public function canonicalClassName($className)
    {
        return $className;
    }
}