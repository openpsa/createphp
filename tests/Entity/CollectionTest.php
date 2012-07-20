<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Entity\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_setController()
    {
        $controller = new Controller(new MockMapper);
        $controller->setVocabulary('test', 'http:://test.org/');
        $collection = new Collection(array(), 'test');
        $collection->setController($controller);
        $this->assertEquals($controller, $collection->getController());
        $this->assertEquals('http:://test.org/', $collection->getAttribute('xmlns:test'));
    }

    public function test_loadFromParent()
    {
        $mapper = new MockMapper;
        $parent_controller = new Controller($mapper);
        $child_controller = new Controller($mapper);
        $collection = new Collection(array(), 'test');
        $collection->setController($child_controller);

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $parent_controller->test = $collection;
        $parent_controller->setObject($parent);
        $children = $parent_controller->test->getChildren();
        $this->assertEquals(2, sizeof($children));
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $children[0]);
        $this->assertEquals('child1', $mapper->createIdentifier($children[0]->getObject()));
    }

    public function test_loadFromParent_no_children()
    {
        $mapper = new MockMapper;
        $parent_controller = new Controller($mapper);
        $child_controller = new Controller($mapper);
        $collection = new Collection(array(), 'test');
        $collection->setController($child_controller);

        $parent = array
        (
            'id' => 'test_id'
        );
        $parent_controller->test = $collection;
        $parent_controller->setObject($parent);
        $children = $parent_controller->test->getChildren();
        $this->assertEquals(1, sizeof($children));
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $children[0]);
        $this->assertEquals('', $mapper->createIdentifier($children[0]->getObject()));
       $this->assertEquals('display:none', $children[0]->getAttribute('style'));
    }

    public function test_Iterator()
    {
        $mapper = new MockMapper;
        $parent_controller = new Controller($mapper);
        $child_controller = new Controller($mapper);
        $collection = new Collection(array(), 'test');
        $collection->setController($child_controller);

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $parent_controller->test = $collection;
        $parent_controller->setObject($parent);
        $i = 0;
        foreach ($parent_controller->test as $key => $child)
        {
            $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $child);
            $i++;
        }
        $this->assertEquals(2, $i);
    }

    public function test_ArrayAccess()
    {
        $mapper = new MockMapper;
        $parent_controller = new Controller($mapper);
        $child_controller = new Controller($mapper);
        $collection = new Collection(array(), 'test');
        $collection->setController($child_controller);

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $parent_controller->test = $collection;
        $parent_controller->setObject($parent);
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $parent_controller->test[0]);
        unset($parent_controller->test[1]);
        $this->assertFalse(isset($parent_controller->test[1]));
        $parent_controller->test[1] = new Collection(array(), 'test2');
        $parent_controller->test[] = new Collection(array(), 'test3');
        $this->assertTrue(isset($parent_controller->test[2]));
    }
}
