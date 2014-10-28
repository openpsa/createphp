<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
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
     * Return the type for the specified name
     *
     * @param string $name
     * @param RdfMapperInterface $mapper
     * @param RdfTypeFactory $typeFactory used to pass into collections
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface the type if found
     *
     * @throws \Midgard\CreatePHP\Metadata\TypeNotFoundException
     */
    public function loadType($name, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory);

    /**
     * Gets a map of rdf types to names with all types known to this driver.
     *
     * @return array of RDF type => name of all types known to this driver.
     */
    public function getAllNames();
}
