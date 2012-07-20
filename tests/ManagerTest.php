<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Manager;
use Midgard\CreatePHP\Widget;
use Midgard\CreatePHP\RestHandler;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_setWidget()
    {
        $manager = new Manager(new MockMapper);
        $widget = new Widget;
        $manager->setWidget($widget);
        $this->assertEquals($widget, $manager->getWidget());
    }

    public function test_get_setController()
    {
        $manager = new Manager(new MockMapper);
        $controller = new Controller(new MockMapper);
        $manager->setController('test', $controller);
        $this->assertEquals($controller, $manager->getType('test'));
    }

    public function test_get_registerWorkflow()
    {
        $manager = new Manager(new MockMapper);
        $workflow = new MockWorkflow;
        $manager->registerWorkflow('mock', $workflow);

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

        $this->assertEquals($expected, $manager->getworkflows('test1'));
    }

    public function test_getRestHandler()
    {
        $manager = new Manager(new MockMapper);
        $service = $manager->getRestHandler();
        $this->assertInstanceOf('Midgard\\CreatePHP\\RestService', $service);
    }
}
