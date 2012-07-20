<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\WorkflowInterface;

/**
 * This is a mock workflow implementation for unittests
 */
class MockWorkflow implements WorkflowInterface
{
    public function getToolbarConfig($object)
    {
        return array
        (
            'name' => "mockbutton",
            'label' => 'Mock Label',
            'action' => array
            (
                'type' => "backbone_destroy"
            ),
            'type' => "button"
        );
    }

    public function run($object)
    {
        return array();
    }
}