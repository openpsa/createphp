<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;
use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Entity\EntityInterface;

/**
 * Mock RdfMapper implementation for unittests
 */
class MockMapper implements RdfMapperInterface
{
    public function setPropertyValue($object, PropertyInterface $node, $value)
    {

    }

    public function getPropertyValue($object, PropertyInterface $node)
    {
        if (isset($object[$node->getIdentifier()]))
        {
            return $object[$node->getIdentifier()];
        }
        return null;
    }

    public function isEditable($object)
    {
        return true;
    }

    public function getChildren($object, CollectionInterface $collection)
    {
        if (isset($object[$collection->getIdentifier()]))
        {
            return $object[$collection->getIdentifier()];
        }
        $config = $collection->getConfig();
        if (empty($config['is_child']))
        {
            throw new \Exception('Wrong configuration or missing data in object array for field "' . $collection->getIdentifier() . '"');
        }
        if (isset($object['children']))
        {
            return $object['children'];
        }
        return array();
    }

    public function prepareObject(TypeInterface $controller, $parent = null)
    {

    }

    public function store(EntityInterface $entity)
    {

    }

    public function canonicalName($className)
    {
        return $className;
    }

    public function getBySubject($identifier)
    {

    }

    public function createSubject($object)
    {
        if (isset($object['id']))
        {
            return $object['id'];
        }
        return '';
    }
}
?>