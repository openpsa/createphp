<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;

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

    public function getChildren($object, array $config)
    {
        if (empty($config['is_child']))
        {
            throw new \Exception('wrong config');
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

    public function store($object)
    {

    }

    public function canonicalClassName($className)
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