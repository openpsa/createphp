<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverXml;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\Entity\Controller;

use Test\Midgard\CreatePHP\Model;

class FunctionalRdfDriverXmlTest extends RdfDriverBase
{
    /**
     * @var \Midgard\CreatePHP\Metadata\RdfDriverInterface
     */
    private $driver;

    private $mapper;

    /**
     * @var RdfTypeFactory
     */
    private $factory;

    public function setUp()
    {
        $this->driver = new RdfDriverXml(array(__DIR__ . DIRECTORY_SEPARATOR . 'rdf'));
        $this->mapper = new \Midgard\CreatePHP\tests\MockMapper();

        $this->factory = new RdfTypeFactory($this->mapper, $this->driver);
    }

    public function testBind()
    {
        $type = $this->factory->getTypeByRdf('http://rdfs.org/sioc/ns#Post');
        $this->assertInstanceOf('Midgard\\CreatePHP\\Entity\\EntityInterface', $type);
        $object = array(
            'title' => 'title',
            'content' => 'content',
            'tags'  => array('tee', 'too'),
            'children' => array('title' => 'child', 'content' => 'childcontent'),
        );
        $type->createWithObject($object);
    }
}
