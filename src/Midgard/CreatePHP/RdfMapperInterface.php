<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Entity\PropertyInterface;
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
    function setPropertyValue($object, PropertyInterface $node, $value);

    /**
     * Get property from this object
     *
     * @param mixed $object
     * @param PropertyInterface $node
     *
     * @return mixed
     */
    function getPropertyValue($object, PropertyInterface $node);

    /**
     * Tell if the object is editable
     *
     * @param mixed $object the data object to check
     *
     * @return boolean
     */
    function isEditable($object);

    /**
     * Get object's children
     *
     * @param mixed $object
     * @param array $config
     *
     * @return array of children objects
     */
    function getChildren($object, array $config);

    /**
     * Instantiate a new object for the specified RDFa type
     *
     * Used as empty template for collections, and to instantiate an empty
     * object when storing a new entity.
     *
     * @param TypeInterface $type
     * @param mixed $parent
     *
     * @return mixed the object
     */
    function prepareObject(TypeInterface $controller, $parent = null);

    /**
     * Save object
     *
     * @param mixed $object
     *
     * @return boolean whether storing was successful
     */
    function store($object);

    /**
     * Load object by identifier
     *
     * @param string $identifier
     * @return mixed The storage object or false if nothing is found
     */
    function getByIdentifier($identifier);

    /**
     * Create RDFa identifier for this object (could be simply the id, but should be a URI)
     *
     * @param mixed $object
     *
     * @return string
     */
    function createIdentifier($object);
}
