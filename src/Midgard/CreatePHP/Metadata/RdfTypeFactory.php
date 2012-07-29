<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;

/**
 * Credit: This metadata loading concept is inspired by the Doctrine Commons mapping loading
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
     * @return \Midgard\CreatePHP\Type\TypeInterface
     */
    public function getType($className)
    {
        if (isset($this->loadedTypes[$className])) {
            return $this->loadedTypes[$className];
        }

        // TODO: combine types from parent models...

        $type = $this->driver->loadTypeForClass($className, $this->mapper, $this);

        if (! is_null($type)) {
            $this->loadedTypes[$className] = $type;
            return $type;
        }

        throw new \Exception("No type found for $className");
    }
}
