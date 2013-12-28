<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverArray;
use Midgard\CreatePHP\Entity\Controller;

class RdfDriverArrayTest extends RdfDriverBase
{
    /**
     * @var \Midgard\CreatePHP\Metadata\RdfDriverInterface
     */
    private $driver;

    public function setUp()
    {
        $def = array(
            "Test\\Midgard\\CreatePHP\\Model" => array (
               "vocabularies" => array(
                   "sioc" => "http://rdfs.org/sioc/ns#",
                   "dcterms" => "http://purl.org/dc/terms/",
               ),
               "typeof" => "sioc:Post",
               "rev" => array("dcterms:partOf"),
               "config" => array(
                   "test" => "testvalue",
               ),
               "children" => array(
                   "title" => array(
                       "nodeType" => "property",
                       "property" => "dcterms:title",
                       "tag-name" => "h2",
                   ),
                   "tags" => array(
                       "nodeType" => "collection",
                       "rel" => "skos:related",
                       "tag-name" => "ul",
                       "config" => array(
                           "table" => "tags",
                       ),
                       "attributes" => array(
                           "class" => "tags",
                       )
                   ),
                   "children" => array(
                       "nodeType" => "collection",
                       "rel" => "dcterms:hasPart",
                       "childtypes" => array(
                           'sioc:Item',
                       ),
                   ),
                   "content" => array(
                       "type" => "property",
                       "property" => "sioc:content",
                   ),
               ),
            ),
            );
        $this->driver = new RdfDriverArray($def);
    }

    public function testLoadType()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $typeFactory = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $itemType = new Controller($mapper);
        $itemType->addRev('my:customRev');
        $typeFactory->expects($this->exactly(2))
            ->method('getTypeByRdf')
            ->with('http://rdfs.org/sioc/ns#Item')
            ->will($this->returnValue($itemType))
        ;

        $type = $this->driver->loadType('Test\\Midgard\\CreatePHP\\Model', $mapper, $typeFactory);

        $this->assertTestNodetype($type);
    }

    /**
     * @expectedException \Midgard\CreatePHP\Metadata\TypeNotFoundException
     */
    public function testLoadTypeForClassNodefinition()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $typeFactory = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $type = $this->driver->loadType('Midgard\\CreatePHP\\Not\\Existing\\Class', $mapper, $typeFactory);
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

    /**
     * Gets the names of all revs known to this type.
     *
     * @return array The names of all revs known to this type.
     */
    public function testGetRevOptions()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $typeFactory = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $type = $this->driver->loadType('Test\\Midgard\\CreatePHP\\Model', $mapper, $typeFactory);

        $revs = array(
            'dcterms:partOf' => 'dcterms:partOf',
        );
        $this->assertEquals($revs, $type->getRevOptions());
    }
}
