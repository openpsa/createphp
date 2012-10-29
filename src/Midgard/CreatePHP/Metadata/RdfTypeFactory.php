<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
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

    /**
     * Get the type responsible for this class
     *
     * @param string $classname name of the model class to get type for
     *
     * @return TypeInterface
     */
    public function getType($className)
    {
        $className = $this->mapper->canonicalClassName($className);
        if (!isset($this->loadedTypes[$className])) {
            $this->loadedTypes[$className] = $this->driver->loadTypeForClass($className, $this->mapper, $this);
        }

        // TODO: combine types from parent models...

        return $this->loadedTypes[$className];
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
        $map = $this->driver->getAllClassNames();
        if (isset($map[$rdf])) {
            return $this->getType($map[$rdf]);
        }
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
