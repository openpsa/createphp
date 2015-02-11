<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * Factory for createphp types based on class names.
 *
 * Credits: This metadata loading concept is inspired by the Doctrine Commons mapping loading.
 *
 * @package Midgard.CreatePHP
 */
class RdfTypeFactory
{
    /**
     * @var RdfMapperInterface
     */
    private $mapper;

    /**
     * @var RdfDriverInterface
     */
    private $driver;

    private $loadedTypes = array();

    /**
     * @param RdfMapperInterface $mapper the mapper to use in this project
     */
    public function __construct(RdfMapperInterface $mapper, RdfDriverInterface $driver)
    {
        $this->mapper = $mapper;
        $this->driver = $driver;
    }

    public function getTypeByObject($object)
    {
        return $this->getTypeByName(
            $this->mapper->objectToName($object)
        );
    }

    /**
     * Get the type with this name
     *
     * @param string $name of the type to get, i.e. the full class name
     *
     * @return TypeInterface
     */
    public function getTypeByName($name)
    {
        if (!isset($this->loadedTypes[$name])) {
            $this->loadedTypes[$name] = $this->driver->loadType($name, $this->mapper, $this);
        }

        // TODO: combine types from parent models...

        return $this->loadedTypes[$name];
    }

    /**
     * Get the type information by (full) RDF name
     *
     * @param string $rdf
     *
     * @return TypeInterface
     */
    public function getTypeByRdf($rdf)
    {
        $map = $this->driver->getAllNames();
        if (! isset($map[$rdf])) {
            throw new TypeNotFoundException("No type for $rdf");
        }
        return $this->getTypeByName($map[$rdf]);
    }

    /**
     * Return all loaded types
     *
     * @return array:
     */
    public function getLoadedTypes()
    {
        return $this->loadedTypes;
    }
}
