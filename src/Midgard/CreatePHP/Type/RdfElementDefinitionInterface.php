<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

/**
 * Common base interface for all type definitions
 *
 * @package Midgard.CreatePHP
 */

interface RdfElementDefinitionInterface
{
    /**
     * Get client configuration array.
     *
     * This can be used to transport application specific information (table
     * names, mappings etc) with the type information. It is typically set
     * in the constructor of type classes.
     *
     * @return array with client configuration
     */
    function getConfig();
}