<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Widget;

class WidgetTest extends \PHPUnit_Framework_TestCase {
    public function testMethods() {
        $wgt = new Widget();
        $this->assertTrue(method_exists($wgt, 'render'));
    }
}
