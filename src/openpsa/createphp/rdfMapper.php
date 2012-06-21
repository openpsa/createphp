<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;
use openpsa\createphp\entity\property;
use openpsa\createphp\entity\controller;

/**
 * Interface for rdfMapper implementations
 *
 * @package openpsa.createphp
 */
interface rdfMapper
{
    /**
     * Set object property
     *
     * @param mixed $object
     * @param property $node
     * @param mixed $value
     * @return mixed
     */
    public function set_property_value($object, property $node, $value);

    /**
     * Get object property
     *
     * @param mixed $object
     * @param property $node
     * @return mixed
     */
    public function get_property_value($object, property $node);

    public function is_editable($object);

    /**
     * Get object's children
     *
     * @param mixed $object
     * @param array $config
     * @return array
     */
    public function get_children($object, array $config);

    public function prepare_object(controller $controller, $parent = null);

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
    public function get_by_identifier($identifier);

    /**
     * Create identifier for passed object
     *
     * @param mixed $object
     * @return string
     */
    public function create_identifier($object);
}
?>