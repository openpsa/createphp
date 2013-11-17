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
    private $defaultMapper;

    /**
     * @var RdfMapperInterface[]
     */
    private $mappers;

    /**
     * @var RdfDriverInterface
     */
    private $driver;

    private $loadedTypes = array();

    /**
     * @param RdfMapperInterface $defaultMapper the default mapper to use if there is no specific one
     * @param RdfDriverInterface $driver the driver to load types from
     * @param RdfMapperInterface[] $mappers rdf mappers per type name
     */
    public function __construct(RdfMapperInterface $defaultMapper, RdfDriverInterface $driver, $mappers = array())
    {
        $this->defaultMapper = $defaultMapper;
        $this->driver = $driver;
        $this->mappers = $mappers;
    }

    public function getTypeByObject($object)
    {
        return $this->getTypeByName(
            $this->driver->objectToName($object, $this->defaultMapper)
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
            $mapper = $this->getMapper($name);
            $this->loadedTypes[$name] = $this->driver->loadType($name, $mapper, $this);
        }

        // TODO: combine types from parent models...

        return $this->loadedTypes[$name];
    }

    /**
     * Get the mapper for type $name, or the defaultMapper if there is no specific mapper.
     *
     * @param string $name the type name for which to get the mapper
     *
     * @return RdfMapperInterface
     */
    protected function getMapper($name)
    {
        return isset($this->mappers[$name]) ? $this->mappers[$name] : $this->defaultMapper;
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
