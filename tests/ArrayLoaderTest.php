<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Entity\Collection;
use Midgard\CreatePHP\ArrayLoader;

class ArrayLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function test_single_property()
    {
        $config = array
        (
            'types' => array
            (
                'test1' => array
                (
                    'children' => array
                    (
                        'test1' => array
                        (
                            'type' => 'property',
                            'attributes' => array
                            (
                                'class' => 'test_prop'
                            )
                        )
                    )
                )
            )
        );
        $mapper = new MockMapper;
        $loader = new ArrayLoader($config);
        $manager = $loader->getManager($mapper);
        $this->assertInstanceOf('Midgard\\CreatePHP\\Manager', $manager);
        $type = $manager->getType('test1');
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\TypeInterface', $type);
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\PropertyDefinitionInterface', $type->test1);
        $this->assertEquals($mapper, $type->getMapper());
        $this->assertEquals(array('createphp' => 'http://openpsa2.org/createphp/'), $type->getVocabularies());
        $this->assertEquals('test_prop', $type->test1->getAttribute('class'));
        $this->assertEquals('createphp:test1', $type->test1->getAttribute('property'));
    }

    public function test_collection_property()
    {
        $config = array
        (
            'types' => array
            (
                'test1' => array
                (
                    'children' => array
                    (
                        'test1' => array
                        (
                            'type' => 'collection',
                            'controller' => 'test2',
                            'attributes' => array
                            (
                                'controller' => 'test2',
                            )
                        )
                    )
                ),
                'test2' => array
                (
                    'children' => array
                    (
                        'test1' => array
                        (
                            'type' => 'property',
                            'attributes' => array
                            (
                                'class' => 'test_prop'
                            )
                        )
                    )
                )

            )
        );
        $mapper = new MockMapper;
        $loader = new ArrayLoader($config);
        $manager = $loader->getManager($mapper);
        $type = $manager->getType('test1');
        $child_type = $manager->getType('test2');
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\TypeInterface', $type);
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\TypeInterface', $child_type);
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\PropertyDefinitionInterface', $child_type->test1);
        $this->assertEquals($child_type, $type->test1->getType());
        $this->assertInstanceOf('Midgard\\CreatePHP\\Type\\CollectionDefinitionInterface', $type->test1);
    }

    public function test_workflows()
    {
        $config = array
        (
            'types' => array(),
            'workflows' => array
            (
                'mock' => 'Midgard\\CreatePHP\\tests\\MockWorkflow'
            )
        );
        $mapper = new MockMapper;
        $loader = new ArrayLoader($config);
        $manager = $loader->getManager($mapper);

        $workflows = $manager->getWorkflows('test_id');

        $expected = array
        (
            array
            (
                'name' => "mockbutton",
                'label' => 'Mock Label',
                'action' => array
                (
                    'type' => "backbone_destroy"
                ),
                'type' => "button"
            )
        );

        $this->assertEquals($expected, $workflows);
    }

}
