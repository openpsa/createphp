<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Entity\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Midgard\CreatePHP\Type\TypeInterface
     */
    private $parentController;
    /**
     * @var \Midgard\CreatePHP\Type\TypeInterface
     */
    private $childController;
    private $mockTypeFactory;
    /** @var \Midgard\CreatePHP\RdfMapperInterface */
    private $mockMapper;

    public function setUp()
    {
        $this->mockMapper = new MockMapper;
        $this->parentController = new Controller($this->mockMapper);
        $this->parentController->setVocabulary('test', 'http:://test.org/');
        $this->childController = new Controller($this->mockMapper);

        $this->mockTypeFactory = $this
            ->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockTypeFactory->expects($this->any())
            ->method('getType')
            ->with('test')
            ->will($this->returnValue($this->childController))
        ;
    }

    public function test_get_setType()
    {
        $collection = new Collection('test', $this->mockTypeFactory, array());
        $collection->setType('test');
        $this->assertEquals('test', $collection->getType());
    }

    public function test_loadFromParent()
    {
        $collection = new Collection('test', $this->mockTypeFactory, array('is_child' => true));
        $collection->setType('test');

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $this->parentController->test = $collection;
        $entity = $this->parentController->createWithObject($parent);
        $children = $entity->test->getChildren();
        $this->assertEquals(2, sizeof($children));
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $children[0]);
        $this->assertEquals('child1', $this->mockMapper->createSubject($children[0]->getObject()));
    }

    public function test_loadFromParent_no_children()
    {
        $collection = new Collection('test', $this->mockTypeFactory, array('is_child' => true));
        $collection->setType('test');

        $parent = array
        (
            'id' => 'test_id'
        );
        $this->parentController->test = $collection;
        $entity = $this->parentController->createWithObject($parent);
        $children = $entity->test->getChildren();
        $this->assertEquals(1, sizeof($children));
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $children[0]);
        $this->assertEquals('', $this->mockMapper->createSubject($children[0]->getObject()));
       $this->assertEquals('display:none', $children[0]->getAttribute('style'));
    }

    public function test_Iterator()
    {
        $collection = new Collection('test', $this->mockTypeFactory, array('is_child' => true));
        $collection->setType('test');

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $this->parentController->test = $collection;
        $entity = $this->parentController->createWithObject($parent);
        $i = 0;
        foreach ($entity->test as $key => $child)
        {
            $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $child);
            $i++;
        }
        $this->assertEquals(2, $i);
    }

    public function test_ArrayAccess()
    {
        $collection = new Collection('test', $this->mockTypeFactory, array('is_child' => true));
        $collection->setType('test');

        $parent = array
        (
            'id' => 'test_id',
            'children' => array
            (
                array('id' => 'child1'),
                array('id' => 'child2')
            )
        );
        $this->parentController->test = $collection;
        $entity = $this->parentController->createWithObject($parent);
        $this->assertInstanceOf('Midgard\CreatePHP\Entity\Controller', $entity->test[0]);
        unset($entity->test[1]);
        $this->assertFalse(isset($entity->test[1]));
        $entity->test[1] = new Collection('test2', $this->mockTypeFactory, array());
        $entity->test[] = new Collection('test3', $this->mockTypeFactory, array());
        $this->assertTrue(isset($entity->test[2]));
    }
}
