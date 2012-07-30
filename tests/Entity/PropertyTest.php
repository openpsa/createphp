<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;
use Midgard\CreatePHP\Entity\Property;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function test_getIdentifier()
    {
        $property = new Property('test', array());
        $this->assertEquals('test', $property->getIdentifier());
    }

    public function test_get_setValue()
    {
        $property = new Property('test', array());
        $this->assertEquals('', $property->getValue());
        $property->setValue('value');
        $this->assertEquals('value', $property->getValue());
    }

    public function test_renderContent()
    {
        $property = new Property('test', array());
        $this->assertEquals('', $property->renderContent());
        $property->setValue('value');
        $this->assertEquals('value', $property->renderContent());
    }

    public function test_render_standalone()
    {
        $controller = new Controller(new MockMapper);
        $controller->test = new Property('test', array());
        $this->assertEquals("<div><div></div>\n</div>\n", $controller->render());
        $controller->test->setValue('value');
        $this->assertEquals("<div><div>value</div>\n</div>\n", $controller->render());
    }

    public function test_render_standalone_with_object()
    {
        $controller = new Controller(new MockMapper);
        $controller->test = new Property('test', array());
        $controller->setObject(array('id' => 'test_id', 'test' => 'value'));

        $this->assertEquals("<div about=\"test_id\"><div>value</div>\n</div>\n", $controller->render());
    }
}
