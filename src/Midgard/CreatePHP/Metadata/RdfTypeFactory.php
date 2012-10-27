<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Symfony\Component\Process\Exception\RuntimeException;

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
     * @var array of rdf type => class name
     */
    protected $typeMap;

    /**
     * @param RdfMapperInterface $mapper the mapper to use in this project
     */
    public function __construct(RdfMapperInterface $mapper, RdfDriverInterface $driver, $typMap)
    {
        $this->mapper = $mapper;
        $this->driver = $driver;
        $this->typeMap = $typMap;
    }

    /**
     * Get the type responsible for this class
     *
     * @param string $classname name of the model class to get type for
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface
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
     * Get the type responsible for this class with the expanded RDF type
     *
     * @param $rdfType  RDF type with full namespace
     * @return \Midgard\CreatePHP\Type\TypeInterface
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function getTypeByRdf($rdfType)
    {
        foreach ($this->typeMap as $type => $className) {
            if ($type === $rdfType) {
                return $this->getType($className);
            }
        }

        throw new RuntimeException('No class mapping found for type ' . $rdfType);
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
