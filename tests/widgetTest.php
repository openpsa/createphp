<?php
namespace openpsa\createphp\tests;

use openpsa\createphp\widget;

class widgetTest extends \PHPUnit_Framework_TestCase {
    public function testMethods() {
        $wgt = new widget();
        $this->assertTrue(method_exists($wgt, 'render')); 
    }
}
