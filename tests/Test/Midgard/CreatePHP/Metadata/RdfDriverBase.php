<?php

namespace Test\Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Metadata\RdfDriverXml;

abstract class RdfDriverBase extends \PHPUnit_Framework_TestCase
{

    /**
     * Assert that this type is properly loaded according to the
     * test type definition:
     *
     * * typeof sioc:Post
     * * two namespaces, one of them sioc
     * * two properties title and content
     *
     * @param mixed $type expected to be of type TypeInterface
     */
    protected function assertTestNodetype($type)
    {
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\TypeInterface', $type);

        $voc = $type->getVocabularies();
        $this->assertCount(2, $voc);
        $this->assertArrayHasKey('sioc', $voc);
        $this->assertEquals('http://rdfs.org/sioc/ns#', $voc['sioc']);
        $this->assertArrayHasKey('dcterms', $voc);
        $this->assertEquals($voc['dcterms'], 'http://purl.org/dc/terms/');

        $this->assertEquals('sioc:Post', $type->getRdfType());

        $this->assertEquals(array('test' => 'testvalue'), $type->getConfig());

        $children = $type->getChildren();
        $this->assertCount(3, $children);

        $this->assertEquals(array('title', 'tags', 'content'), array_keys($children));
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\PropertyDefinitionInterface', $children['title']);
        $this->assertEquals('title', $children['title']->getIdentifier());
        $this->assertEquals('dcterms:title', $children['title']->getAttribute('property'));
        $this->assertEquals('h2', $children['title']->getTagName());

        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\PropertyDefinitionInterface', $children['content']);
        $this->assertEquals('content', $children['content']->getIdentifier());
        $this->assertEquals('sioc:content', $children['content']->getAttribute('property'));

        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\CollectionDefinitionInterface', $children['tags']);
        $this->assertEquals('tags', $children['tags']->getIdentifier());
        $this->assertEquals('skos:related', $children['tags']->getAttribute('rel'));
        $this->assertEquals('ul', $children['tags']->getTagName());
        $this->assertEquals(array('table' => 'tags'), $children['tags']->getConfig());
    }
}