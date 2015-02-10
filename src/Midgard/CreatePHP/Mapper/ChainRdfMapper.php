<?php

namespace Midgard\CreatePHP\Mapper;

use Midgard\CreatePHP\RdfChainableMapperInterface;
use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;
use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\EntityInterface;
use RuntimeException;

/**
 * Looks at all registered mappers to find one that can handle objects.
 *
 * @package Midgard.CreatePHP
 */
class ChainRdfMapper implements RdfMapperInterface
{
    /**
     * All the registered mappers
     *
     * @var RdfMapperInterface[]
     */
    private $mappers = array();

    /**
     * Mappers index by the spl_object_hash of objects created by CreatePHP
     *
     * @var RdfMapperInterface[]
     */
    private $createdObjects = array();

    /**
     * Register a mapper with a key. The key will be prefixed to all subjects.
     *
     * @param RdfChainableMapperInterface $mapper
     * @param string                      $mapperKey
     */
    public function registerMapper(RdfChainableMapperInterface $mapper, $mapperKey)
    {
        $this->mappers[$mapperKey] = $mapper;
    }

    /**
     * Get the mapper than can handle object.
     *
     * @param  mixed                       $object
     * @return RdfChainableMapperInterface
     * @throws RuntimeException            when no mapper can handle the object
     */
    protected function getMapperForObject($object)
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($object)) {
                return $mapper;
            }
        }

        $hash = spl_object_hash($object);
        if (isset($this->createdObjects[$hash])) {
            return $this->createdObjects[$hash];
        }

        throw new RuntimeException("No mapper can create a subject for object.");
    }
    /**
     * {@inheritdoc}
     */
    public function setPropertyValue($object, PropertyInterface $property, $value)
    {
        return $this->getMapperForObject($object)->setPropertyValue($object, $property, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyValue($object, PropertyInterface $property)
    {
        return $this->getMapperForObject($object)->getPropertyValue($object, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable($object)
    {
        return $this->getMapperForObject($object)->isEditable($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($object, CollectionInterface $collection)
    {
        return $this->getMapperForObject($object)->getChildren($object, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function canonicalName($className)
    {
        return $className;
    }

    /**
     * {@inheritdoc}
     */
    public function objectToName($object)
    {
        return $this->getMapperForObject($object)->objectToName($object);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareObject(TypeInterface $controller, $parent = null)
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supportsCreate($controller)) {
                $object = $mapper->prepareObject($controller, $parent);
                $this->createdObjects[spl_object_hash($object)] = $mapper;

                return $object;
            }
        }

        throw new RuntimeException(sprintf('None of the registered mappers can create an object of type %s', $controller->getRdfType()));
    }

    /**
     * {@inheritdoc}
     */
    public function store(EntityInterface $entity)
    {
        return $this->getMapperForObject($entity->getObject())->store($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getBySubject($subject)
    {
        list($mapperKey, $mapperSubject) = explode('|', $subject, 2);

        if (!isset($this->mappers[$mapperKey])) {
            throw new RuntimeException("Invalid subject: $subject");
        }

        $object = $this->mappers[$mapperKey]->getBySubject($mapperSubject);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubject($object)
    {
        foreach ($this->mappers as $mapperKey => $mapper) {
            if ($mapper->supports($object)) {
                return $mapperKey.'|'.$mapper->createSubject($object);
            }
        }

        throw new RuntimeException(sprintf('None of the registered mappers can create the subject for object of class %s', get_class($object)));
    }

    /**
     * {@inheritdoc}
     */
    public function orderChildren(EntityInterface $entity, CollectionInterface $node, $expectedOrder)
    {
        return $this->getMapperForObject($entity->getObject())->orderChildren($entity, $node, $expectedOrder);
    }
}
