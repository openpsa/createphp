<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
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
    function getToolbarConfig($object);

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
    function run($object);
}
