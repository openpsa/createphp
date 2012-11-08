<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Manager;
use Midgard\CreatePHP\Widget;
use Midgard\CreatePHP\RestService;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var \Midgard\CreatePHP\Metadata\RdfTypeFactory
     */
    private $factoryMock;

    public function setUp()
    {
        $this->factoryMock = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $this->manager = new Manager(new MockMapper, $this->factoryMock);
    }
    public function test_get_setWidget()
    {
        $widget = new Widget($this->manager);
        $this->manager->setWidget($widget);
        $this->assertEquals($widget, $this->manager->getWidget());
    }

    public function test_get_setController()
    {
        $controller = new Controller(new MockMapper);
        $this->factoryMock
            ->expects($this->once())
            ->method('getTypeByName')
            ->with('test')
            ->will($this->returnValue($controller))
        ;
        $this->assertEquals($controller, $this->manager->getType('test'));
    }

    public function test_get_registerWorkflow()
    {
        $workflow = new MockWorkflow;
        $this->manager->registerWorkflow('mock', $workflow);

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

        $this->assertEquals($expected, $this->manager->getworkflows('test1'));
    }

    public function test_getRestHandler()
    {
        $service = $this->manager->getRestHandler();
        $this->assertInstanceOf('Midgard\\CreatePHP\\RestService', $service);
    }
}
