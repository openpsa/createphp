<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

/**
 * Interface for workflow implementations
 *
 * @package Midgard.CreatePHP
 */
interface WorkflowInterface
{
    /**
     * Get toolbar config for the given object, if the workflow is applicable
     * and allowed.
     *
     * @see http://createjs.org/guide/#workflows
     *
     * @param mixed $object
     * @return array|null array to return for this workflow, or null if workflow is not allowed
     */
    public function getToolbarConfig($object);

    /**
     * Execute this workflow
     *
     * The object will only be set if there is a subject parameter in $_GET
     * that can be found by the mapper tied to the RestService
     *
     * @param mixed $object
     *
     * @return array TODO what?
     */
    public function run($object);
}
