<?php

namespace Test\Midgard\CreatePHP;

use Midgard\CreatePHP\RestService;
use Midgard\CreatePHP\Entity\EntityInterface;

class RestServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $child_type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $property;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    public function setUp()
    {
        $this->mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $this->type = $this->getMock('Midgard\\CreatePHP\\Type\\TypeInterface');
        $this->child_type = $this->getMock('Midgard\\CreatePHP\\Type\\TypeInterface');
        $this->entity = $this->getMock('Midgard\\CreatePHP\\Entity\\EntityInterface');
        $this->property = $this->getMock('Midgard\\CreatePHP\\Entity\\PropertyInterface');
        $this->collection = $this->getMock('Midgard\\CreatePHP\\Entity\\CollectionInterface');

        $this->mapper->expects($this->once())
            ->method('store')
            ->with($this->entity)
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->once())
            ->method('createSubject')
            ->with('testmodel')
            ->will($this->returnValue('/the/subject'))
        ;
        $this->mapper->expects($this->once())
            ->method('setPropertyValue')
            ->with('testmodel', $this->equalTo($this->property), 'the title')
            ->will($this->returnValue('testmodel'))
        ;
        $this->mapper->expects($this->once())
            ->method('getPropertyValue')
            ->with('testmodel', $this->equalTo($this->property))
            ->will($this->returnValue('stored title')) // The data storage could have changed the value
        ;
        $this->type->expects($this->any())
            ->method('getVocabularies')
            ->will($this->returnValue(array('dcterms' => 'http://purl.org/dc/terms/')))
        ;
        $this->type->expects($this->any())
            ->method('getChildDefinitions')
            ->will($this->returnValue(array('title' => $this->property, 'children' => $this->collection)))
        ;

        $this->child_type->expects($this->any())
            ->method('getVocabularies')
            ->will($this->returnValue(array('dcterms' => 'http://purl.org/dc/terms/')))
        ;
        $this->child_type->expects($this->any())
            ->method('createWithObject')
            ->with('testmodel')
            ->will($this->returnValue($this->entity))
        ;

        $this->entity->expects($this->any())
            ->method('getChildDefinitions')
            ->will($this->returnValue(array('title' => $this->property, 'children' => $this->collection)))
        ;
        $this->entity->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue('testmodel'))
        ;
        $this->entity->expects($this->any())
            ->method('getVocabularies')
            ->will($this->returnValue(array('dcterms' => 'http://purl.org/dc/terms/')))
        ;
        $this->property->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue('dcterms:title'))
        ;

        $this->collection->expects($this->any())
            ->method('getRev')
            ->will($this->returnValue('dcterms:partOf'))
        ;

        $this->collection->expects($this->any())
          ->method('getRel')
          ->will($this->returnValue('dcterms:section'))
        ;

        $this->collection->expects($this->any())
            ->method('getTypes')
            ->will($this->returnValue(array($this->child_type)))
        ;
    }

    public function testRunPut()
    {
        $this->type->expects($this->once())
            ->method('createWithObject')
            ->with('testmodel')
            ->will($this->returnValue($this->entity))
        ;

        $this->mapper->expects($this->once())
            ->method('getBySubject')
            ->with('/the/subject')
            ->will($this->returnValue('testmodel'))
        ;

        $rest = new RestService($this->mapper);

        $data = array('@subject' => '</the/subject>', '<http://purl.org/dc/terms/title>' => 'the title', '<http://purl.org/dc/terms/partOf>' => array('</parent/subject>'));

        $return = $rest->run($data, $this->type, '/the/subject', RestService::HTTP_PUT);

        $this->assertEquals(array(
                '@subject' => '</the/subject>',
                '<http://purl.org/dc/terms/title>' => 'stored title',
            ), $return
        );
    }

    public function testRunPost()
    {
        $this->mapper->expects($this->once())
            ->method('prepareObject')
            ->with($this->equalTo($this->child_type))
            ->will($this->returnValue('testmodel'))
        ;

        $this->type->expects($this->any())
            ->method('getRevOptions')
            ->will($this->returnValue(array('dcterms:partOf')))
        ;

        $this->type->expects($this->any())
            ->method('createWithObject')
            ->will($this->returnValue($this->entity))
        ;

        $rest = new RestService($this->mapper);

        $data = array('<http://purl.org/dc/terms/title>' => 'the title', '<http://purl.org/dc/terms/partOf>' => array('</parent/subject>'));

        $return = $rest->run($data, $this->type, null, RestService::HTTP_POST);

        $this->assertEquals(array(
                '@subject' => '</the/subject>',
                '<http://purl.org/dc/terms/title>' => 'stored title'
            ), $return
        );
    }
}