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

    }

    public function isEditable($object)
    {

    }

    public function getChildren($object, array $config)
    {

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

    }
}
?>