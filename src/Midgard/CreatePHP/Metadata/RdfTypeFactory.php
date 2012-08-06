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
     * @return \Midgard\CreatePHP\Type\TypeInterface
     */
    public function getType($className)
    {
        $className = $this->cleanClassName($className);
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

    /**
     * Clean up the class name, for example if this is a doctrine proxy
     * class, get the real class. Overwrite this to get custom behaviour
     *
     * @param string $className the user supplied class name
     *
     * @return string the real classname to use
     */
    protected function cleanClassName($className)
    {

        if (interface_exists('Doctrine\\Common\\Persistence\\Proxy')) {
            $refl = new \ReflectionClass($className);
            if (in_array('Doctrine\\Common\\Persistence\\Proxy', $refl->getInterfaceNames())) {
                $className = \Doctrine\Common\Util\ClassUtils::getRealClass($className);
            }
        }

        return $className;
    }
}
