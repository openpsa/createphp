<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverXml;

class RdfDriverXmlTest extends \PHPUnit_Framework_TestCase
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
        $type = $this->driver->loadTypeForClass('Test\\Midgard\\CreatePHP\\Model', $mapper);

        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\TypeInterface', $type);
        $voc = $type->getVocabularies();
        $this->assertCount(2, $voc);
        $this->assertTrue(isset($voc['sioc']));

        $children = $type->getChildren();
        $this->assertCount(2, $children);

        $this->assertEquals(array('title', 'content'), array_keys($children));
        $this->assertEquals('title', $children['title']->getIdentifier());
        $this->assertEquals('dcterms:title', $children['title']->getAttribute('property'));
    }

    public function testLoadTypeForClassNodefinition()
    {
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $type = $this->driver->loadTypeForClass('Midgard\\CreatePHP\\Not\\Existing\\Class', $mapper);
        $this->assertNull($type);
    }

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    function testGetAllClassNames()
    {
        // TODO
    }
}
