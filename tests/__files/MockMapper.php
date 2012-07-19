<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Entity\Property;

/**
 * Mock RdfMapper implementation for unittests
 */
class MockMapper implements RdfMapperInterface
{
    public function setPropertyValue($object, Property $node, $value)
    {

    }

    public function getPropertyValue($object, Property $node)
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
        if (isset($object['children']))
        {
            return $object['children'];
        }
        return array();
    }

    public function prepareObject(Controller $controller, $parent = null)
    {

    }

    public function store($object)
    {

    }

    public function getByIdentifier($identifier)
    {

    }

    public function createIdentifier($object)
    {
        if (isset($object['id']))
        {
            return $object['id'];
        }
        return '';
    }
}
?>