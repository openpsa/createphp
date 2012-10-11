<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;

/**
 * Rdf information driver for createphp
 *
 * @package Midgard.CreatePHP
 */
interface RdfDriverInterface
{
    const DEFAULT_VOCABULARY_URI = 'http://openpsa2.org/createphp/';
    const DEFAULT_VOCABULARY_PREFIX = 'createphp';

    /**
     * Return the type for the specified class
     *
     * @param string $className
     * @param RdfMapperInterface $mapper
     * @param RdfTypeFactory $typeFactory used to pass into collections
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface the type if found
     * @throws \Midgard\CreatePHP\Metadata\TypeNotFoundException
     */
    function loadTypeForClass($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory);

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    function getAllClassNames();
}
