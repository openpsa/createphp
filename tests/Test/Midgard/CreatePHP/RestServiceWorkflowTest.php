<?php

namespace Test\Midgard\CreatePHP;

use Midgard\CreatePHP\RestService;
use Midgard\CreatePHP\tests\MockWorkflow;

class RestServiceWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testGetworkflows()
    {
        $workflow = new MockWorkflow;
        $mapper = $this->getMock('Midgard\\CreatePHP\\RdfMapperInterface');

        $rest = new RestService($mapper);

        $rest->setWorkflow('mock', $workflow);

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

        $this->assertEquals($expected, $rest->getWorkflows('test1'));
    }
}