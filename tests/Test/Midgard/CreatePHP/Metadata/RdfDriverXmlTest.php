<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverXml;
use Midgard\CreatePHP\Entity\Controller;

class RdfDriverXmlTest extends RdfDriverBase
{
    /**
     * @var \Midgard\CreatePHP\Metadata\RdfDriverInterface
     */
    private $driver;

    public function setUp()
    {
        $this->driver = new RdfDriverXml(array(__DIR__ . DIRECTORY_SEPARATOR . 'rdf'));
    }

    public function testLoadTypeForClass()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $typeFactory = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $itemType = new Controller($mapper);
        $itemType->addRev('my:customRev');
        $typeFactory->expects($this->once())
            ->method('getTypeByRdf')
            ->with('http://rdfs.org/sioc/ns#Item')
            ->will($this->returnValue($itemType))
        ;

        $type = $this->driver->loadType('Test\\Midgard\\CreatePHP\\Model', $mapper, $typeFactory);

        $this->assertTestNodetype($type);
    }

    /**
     * @expectedException Midgard\CreatePHP\Metadata\TypeNotFoundException
     */
    public function testLoadTypeForClassNodefinition()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $typeFactory = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $this->driver->loadType('Midgard\\CreatePHP\\Not\\Existing\\Class', $mapper, $typeFactory);
    }

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    public function testGetAllNames()
    {
        $map = $this->driver->getAllNames();
        $this->assertCount(1, $map);
        $types = array(
            'http://rdfs.org/sioc/ns#Post' => 'Test\\Midgard\\CreatePHP\\Model',
        );
        $this->assertEquals($types, $map);
    }
}
