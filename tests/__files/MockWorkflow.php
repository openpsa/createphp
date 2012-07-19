<?php
namespace Midgard\CreatePHP\tests;

use Midgard\CreatePHP\Workflow;

/**
 * This is a mock workflow implementation for unittests
 */
class MockWorkflow implements Workflow
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