<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP;
use OpenPSA\CreatePHP\Entity\Property;
use OpenPSA\CreatePHP\Entity\Controller;

/**
 * Interface for rdfMapper implementations
 *
 * @package OpenPSA.CreatePHP
 */
interface RdfMapper
{
    /**
     * Set object property
     *
     * @param mixed $object
     * @param property $node
     * @param mixed $value
     * @return mixed
     */
    public function setPropertyValue($object, property $node, $value);

    /**
     * Get object property
     *
     * @param mixed $object
     * @param property $node
     * @return mixed
     */
    public function getPropertyValue($object, property $node);

    public function isEditable($object);

    /**
     * Get object's children
     *
     * @param mixed $object
     * @param array $config
     * @return array
     */
    public function getChildren($object, array $config);

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
     * Create identifier for passed object
     *
     * @param mixed $object
     * @return string
     */
    public function createIdentifier($object);
}
