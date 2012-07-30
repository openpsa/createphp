<?php

namespace Test\Midgard\CreatePHP;

use Midgard\CreatePHP\RestService;

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
    private $entity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $property;

    public function setUp()
    {
        $this->mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');
        $this->type = $this->getMock('Midgard\\CreatePHP\\Type\\TypeInterface');
        $this->entity = $this->getMock('Midgard\\CreatePHP\\Entity\\EntityInterface');
        $this->property = $this->getMock('Midgard\\CreatePHP\\Entity\\PropertyInterface');

        $this->mapper->expects($this->once())
            ->method('store')
            ->with('testmodel')
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

        $this->type->expects($this->once())
            ->method('createWithObject')
            ->with('testmodel')
            ->will($this->returnValue($this->entity))
        ;
        $this->type->expects($this->any())
            ->method('getVocabularies')
            ->will($this->returnValue(array('dcterms' => 'http://purl.org/dc/terms/')))
        ;

        $this->entity->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue(array('title' => $this->property)))
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
            ->method('getAttribute')
            ->with('property')
            ->will($this->returnValue('dcterms:title'))
        ;
    }

    public function testRunPut()
    {
        $this->mapper->expects($this->once())
            ->method('getBySubject')
            ->with('/the/subject')
            ->will($this->returnValue('testmodel'))
        ;

        $rest = new RestService($this->mapper);

        $data = array('@subject' => '</the/subject>', '<http://purl.org/dc/terms/title>' => 'the title');

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
            ->with($this->equalTo($this->type))
            ->will($this->returnValue('testmodel'))
        ;

        $this->type->expects($this->any())
            ->method('getChildDefinitions')
            ->will($this->returnValue(array('title' => $this->property)))
        ;

        $rest = new RestService($this->mapper);

        $data = array('<http://purl.org/dc/terms/title>' => 'the title');

        $return = $rest->run($data, $this->type, null, RestService::HTTP_POST);

        $this->assertEquals(array(
                '@subject' => '</the/subject>',
                '<http://purl.org/dc/terms/title>' => 'stored title',
            ), $return
        );
    }
}