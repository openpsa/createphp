<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Widget;
use Midgard\CreatePHP\Manager;

class WidgetTest extends \PHPUnit_Framework_TestCase {

    public function testMethods() {
        $this->factoryMock = $this->getMockBuilder('Midgard\\CreatePHP\\Metadata\\RdfTypeFactory')->disableOriginalConstructor()->getMock();
        $manager = new Manager(new MockMapper, $this->factoryMock);

        $wgt = new Widget($manager);
        $this->assertTrue(method_exists($wgt, 'render'));
    }
}
