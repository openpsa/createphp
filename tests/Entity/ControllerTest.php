<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Entity\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_setVocabulary()
    {
        $controller = new Controller(new MockMapper);
        $controller->setVocabulary('test', 'http://test.org/');
        $vocabularies = $controller->getVocabularies();
        $this->assertEquals(array('test' => 'http://test.org/'), $vocabularies);
        $this->assertEquals('http://test.org/', $controller->getAttribute('xmlns:test'));
    }

    public function test_get_set_unsetAttribute()
    {
        $controller = new Controller(new MockMapper);
        $controller->setAttribute('name', 'value');
        $this->assertEquals('value', $controller->getAttribute('name'));
        $controller->unsetAttribute('name');
        $this->assertNull($controller->getAttribute('name'));
    }

    public function test_getMapper()
    {
        $mapper = new MockMapper;
        $controller = new Controller($mapper);
        $this->assertEquals($mapper, $controller->getMapper());
    }

    public function test_set_isEditable()
    {
        $controller = new Controller(new MockMapper);
        $this->assertTrue($controller->isEditable());
        $controller->setEditable(false);
        $this->assertFalse($controller->isEditable());
    }
}
