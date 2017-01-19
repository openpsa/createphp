<?php

namespace Test\Midgard\CreatePHP\Mapper;

use Midgard\CreatePHP\Mapper\DoctrineOrmMapper;

class DoctrineOrmMapperTest extends \PHPUnit_Framework_TestCase
{
    public function provideIds()
    {
        return array(
            array(
                array('simplekey' => 'simplevalue'),
            ),
            array(
                array(
                    '%|=' => '%|=',
                    '=%|' => '=%|',
                    '=|%' => '=|%',
                    '=' => '=',
                    '|' => '|',
                ),
            )
        );
    }

    /**
     * @dataProvider provideIds
     */
    public function testSubject(array $ids)
    {
        $entity = new MockOrmEntity();

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($ids)
            ->will($this->returnValue($entity))
        ;
        $om = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $om
            ->expects($this->once())
            ->method('getRepository')
            ->with(get_class($entity))
            ->will($this->returnValue($repository))
        ;
        $meta = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $meta
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->will($this->returnValue($ids))
        ;
        $om
            ->expects($this->once())
            ->method('getClassMetaData')
            ->will($this->returnValue($meta))
        ;

        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($om))
        ;

        $mapper = new DoctrineOrmMapper(array(), $registry);
        $subject = $mapper->createSubject($entity);
        $this->assertSame($entity, $mapper->getBySubject($subject));

        $className = str_replace('\\', '-', get_class($entity));
        $this->assertContains($className, $subject);
    }
}

class MockOrmEntity
{
}
