<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;
use Midgard\CreatePHP\Entity\Property;
use Midgard\CreatePHP\Entity\Controller;

/**
 * Map from createphp to your domain objects
 *
 * You can have a mapper per type or a generic mapper that handles all types.
 *
 * @package Midgard.CreatePHP
 */
interface RdfMapperInterface
{
    /**
     * Set property on object
     *
     * @param mixed $object
     * @param Property $node
     * @param mixed $value
     * @return mixed
     */
    public function setPropertyValue($object, Property $node, $value);

    /**
     * Get property from this object
     *
     * @param mixed $object
     * @param Property $node
     * @return mixed
     */
    public function getPropertyValue($object, Property $node);

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
     * @param array $config
     *
     * @return array of children objects
     */
    public function getChildren($object, array $config);

    /**
     * Instantiate a new object for the type defined by this controller
     *
     * Used as empty template for collections, and when storing a new entity.
     *
     * @param Entity\Controller $controller
     * @param mixed $parent
     *
     * @return mixed the object
     */
    public function prepareObject(Controller $controller, $parent = null);

    /**
     * Save object
     *
     * @param mixed $object
     */
    public function store($object);

    /**
     * Load object by identifier
     *
     * @param string $identifier
     * @return mixed The storage object or false if nothing is found
     */
    public function getByIdentifier($identifier);

    /**
     * Create RDFa identifier for this object (could be simply the id, but should be a URI)
     *
     * @param mixed $object
     *
     * @return string
     */
    public function createIdentifier($object);
}
