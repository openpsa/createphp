<?php

namespace Test\Midgard\CreatePHP;

use Midgard\CreatePHP\RestService;
use Midgard\CreatePHP\tests\MockWorkflow;

class RestServiceWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_registerWorkflow()
    {
        $workflow = new MockWorkflow;
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');

        $restHandler = new RestService($mapper);

        $restHandler->registerWorkflow('mock', $workflow);

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

        $this->assertEquals($expected, $restHandler->getWorkflows('test1'));
    }
}