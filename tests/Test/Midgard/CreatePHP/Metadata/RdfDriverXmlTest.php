<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverXml;

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
        $type = $this->driver->loadTypeForClass('Test\\Midgard\\CreatePHP\\Model', $mapper);

        $this->assertTestNodetype($type);
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
