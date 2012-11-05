<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Entity\PropertyInterface;
use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * Map from CreatePHP to your domain objects
 *
 * You can have a mapper per type or a generic mapper that handles all types.
 *
 * @package Midgard.CreatePHP
 */
interface RdfMapperInterface
{
    /**
     * Set property on object and return the updated object
     *
     * @param mixed $object
     * @param PropertyInterface $node
     * @param mixed $value
     *
     * @return mixed the updated object
     */
    public function setPropertyValue($object, PropertyInterface $property, $value);

    /**
     * Get property from this object
     *
     * @param mixed $object
     * @param PropertyInterface $property
     *
     * @return mixed
     */
    public function getPropertyValue($object, PropertyInterface $property);

    /**
     * Tell if the object is editable
     *
     * @param mixed $object the data object to check
     *
     * @return boolean
     */
    public function isEditable($object);

    /**
     * Get object's children
     *
     * @param mixed $object
     * @param array $config configuration of this collection
     *
     * @return array of children objects
     */
    public function getChildren($object, CollectionInterface $collection);

    /**
     * Ensure this classname is in its canonical form so it can be used for
     * lookups. This may include fixing uppercase, removing doctrine proxy
     * and so on.
     *
     * If you do not know what to do, just return the string as passed in.
     *
     * @param string $className a class name as passed to the RDF type factory
     *
     * @return string the canonical name for this class
     */
    public function canonicalClassName($className);

    /**
     * Instantiate a new object for the specified RDFa type
     *
     * Used as empty template for collections, and to instantiate an empty
     * object when storing a new entity.
     *
     * @param TypeInterface $type
     * @param mixed $parent the parent object, if any. used for creating a
     *      collection entry
     *
     * @return mixed the object
     */
    public function prepareObject(TypeInterface $controller, $parent = null);

    /**
     * Save object
     *
     * @param mixed $object
     *
     * @return boolean whether storing was successful
     */
    public function store($object);

    /**
     * Load object by json-ld subject (this is the RDFa about field)
     *
     * @param string $subject
     *
     * @return mixed The storage object or false if nothing is found
     */
    public function getBySubject($subject);

    /**
     * Create json-ld subject (RDFa about) for this object (could be simply the
     * id, but should be a URI)
     *
     * This needs to be unique for your application so you can load the object
     * just from the subject.
     *
     * @param mixed $object
     *
     * @return string
     */
    public function createSubject($object);
}
