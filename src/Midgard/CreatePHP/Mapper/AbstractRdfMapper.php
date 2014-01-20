<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Mapper;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;
use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\RdfElementDefinitionInterface;

/**
 * Base mapper class with utility methods for generic operations.
 *
 * Extend and overwrite for your own mapper.
 *
 * @package Midgard.CreatePHP
 */
abstract class AbstractRdfMapper implements RdfMapperInterface
{
    /**
     * @var array of rdf type => class name
     */
    protected $typeMap;

    /**
     * Create this mapper with a map of rdf type to className to be used in
     * prepareObject. If you do not want to use the map, just overwrite
     * prepareObject.
     *
     * @param array $typeMap of rdf type => className. The rdf types using
     *      full namespaces, i.e. http://rdfs.org/sioc/ns#Post
     */
    public function __construct($typeMap = array())
    {
        $this->typeMap = $typeMap;
    }

    /**
     * {@inheritDoc}
     *
     * Create the object if a class is defined in the typeMap. This class
     * can not know how to set the parent, so if you ever create collection
     * entries, your extending class should handle the parent - it can still
     * call this method to create the basic object, just omit the parent
     * parameter and then set the parent on the returned value. For an example,
     * see DoctrinePhpcrOdmMapper.
     *
     * Just overwrite if you use a different concept.
     */
    public function prepareObject(TypeInterface $type, $parent = null)
    {
        if ($parent !== null) {
            throw new \Exception('Parent is not null, please extend this method to configure the parent');
        }
        list($prefix, $shortname) = explode(':', $type->getRdfType());
        $ns = $type->getVocabularies();
        $ns = $ns[$prefix];
        $name = $ns.$shortname;
        if (isset($this->typeMap[$name])) {
            $class = $this->typeMap[$name];
            return new $class;
        }
        throw new \Exception('No information on ' . $name);
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyValue($object, PropertyInterface $property, $value)
    {
        $class = new \ReflectionClass($object);

        $name = $property->getIdentifier();

        $method = 'set' . ucfirst($name);
        if ($class->hasMethod($method)) {
            $object->$method($value);
        } elseif ($class->hasProperty($name)) {
            $object->$name = $value;
        } else {
            throw new \Exception('Unknown property '.$property->getIdentifier());
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyValue($object, PropertyInterface $property)
    {
        return $this->getField($object, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function isEditable($object)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren($object, CollectionInterface $collection)
    {
        return $this->getField($object, $collection);
    }

    /**
     * Get a field of the class if it exists.
     *
     * @param mixed $object the model object
     * @param RdfElementDefinitionInterface $child the property or collection
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function getField($object, RdfElementDefinitionInterface $child)
    {
        $class = new \ReflectionClass($object);

        $name = $child->getIdentifier();
        $method = 'get' . ucfirst($name);
        if ($class->hasMethod($method)) {
            return $object->$method();
        }
        if ($class->hasProperty($name)) {
            return $object->$name;
        }
        if (is_array($object) && array_key_exists($name, $object)) {
            return $object[$name];
        }

        $typename = is_object($object) ? get_class($object) : gettype($object);
        throw new \Exception('Can not find anything called ' . $child->getIdentifier() . ' on ' . $typename);
    }

    /**
     * A dummy classname canonicalizer: returns the name unmodified.
     *
     * @param string $className
     * @return string exactly the same as $className
     */
    public function canonicalName($className)
    {
        return $className;
    }

    /**
     * This sort method is used for sorting elements in the given array according the reference array
     * which contains some of the array keys of the first array.
     *
     * The method makes sure that array elements without a key in the reference stay in the array with
     * a nearly stable order (i.e. what was before the elements in reference stays before, what was after
     * stays after, what is in is ordered as in reference.
     */
    public function sort($array, $reference)
    {
        $headIdx = 0;
        $tailIdx = 0;
        $i = 0;
        foreach($array as $element) {
            $i++;
            if (false === array_search($element, $reference)) {
                if (0 == $tailIdx) {
                    $headIdx = $i;
                }
            } else {
                $tailIdx = $i;
            }
        }

        $toSort = array_splice($array, $headIdx);
        $tail = array_splice($toSort, $tailIdx - $headIdx);

        for ($i=1; $i < count($toSort); $i++) {
            $tempIdx = (int)array_search($toSort[$i], $reference);
            $temp = $toSort[$i];
            $j = $i - 1;

            while ($j >= 0 && (int)array_search($toSort[$j], $reference) > $tempIdx){
                $toSort[$j + 1] = $toSort[$j];
                $j--;
            }

            $toSort[$j+1] = $temp;
        }

        return array_merge($array, $toSort, $tail);
    }
}