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
            'controllers' => array
            (
                'test1' => array
                (
                    'properties' => array
                    (
                        'test1' => array
                        (
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
        $controller = $manager->getType('test1');
        $this->assertInstanceOf('Midgard\\CreatePHP\\Entity\\Controller', $controller);
        $this->assertInstanceOf('Midgard\\CreatePHP\\Entity\\Property', $controller->test1);
        $this->assertEquals($mapper, $controller->getMapper());
        $this->assertEquals(array('createphp' => 'http://openpsa2.org/createphp/'), $controller->getVocabularies());
        $this->assertEquals('test_prop', $controller->test1->getAttribute('class'));
        $this->assertEquals('createphp:test1', $controller->test1->getAttribute('property'));
    }

    public function test_collection_property()
    {
        $config = array
        (
            'controllers' => array
            (
                'test1' => array
                (
                    'properties' => array
                    (
                        'test1' => array
                        (
                            'nodeType' => 'Midgard\\CreatePHP\\Entity\\Collection',
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
                    'properties' => array
                    (
                        'test1' => array
                        (
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
        $controller = $manager->getType('test1');
        $child_controller = $manager->getType('test2');
        $this->assertEquals($child_controller, $controller->test1->getType());
        $this->assertInstanceOf('Midgard\\CreatePHP\\Entity\\Collection', $controller->test1);

    }

    public function test_workflows()
    {
        $config = array
        (
            'controllers' => array(),
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
