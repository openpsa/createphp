<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\TypeInterface;

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
     * @param array $data the json-ld data received in the request
     * @param TypeInterface $type the type information for this data
     * @param string $subject the request subject for workflows
     * @param string $method the http request method, one of the HTTP constants,
     *      if omitted, $_SERVER['REQUEST_METHOD'] is used
     *
     * @return null|array if this is a successful post or put, returns the json
     *      data for the processed item
     */
    public function run($data, TypeInterface $type, $subject = null, $method = null);
}
