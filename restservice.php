<?php
/**
 * REST service backend
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;

/**
 * @package openpsa.createphp
 */
class restservice
{
    /**
     * The mapper object
     *
     * @var rdfMapper
     */
    protected $_mapper;

    /**
     * The operation verb
     *
     * @var string
     */
    protected $_verb;

    /**
     * The passed data, if any
     *
     * @var array
     */
    protected $_data;

    /**
     * The constructor
     *
     * @param rdfMapper $mapper
     */
    public function __construct(rdfMapper $mapper, array $data = null)
    {
        $this->_data = $data;
        $this->_verb = strtolower($_SERVER['REQUEST_METHOD']);
        $this->set_mapper($mapper);
    }

    public function get_data()
    {
        return $this->_data;
    }

    /**
     * Get transmitted properties
     *
     * @return array
     */
    private function _get_properties()
    {
        $return = array();

        foreach ($this->_data as $key => $value)
        {
            if (substr($key, 0, 1) === '@')
            {
                continue;
            }
            $key = trim($key, '<>');
            $return[$key] = $value;
        }
        return $return;
    }

    /**
     * Mapper setter
     *
     * @param rdfMapper $mapper
     */
    public function set_mapper(rdfMapper $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Mapper getter
     *
     * @return rdfMapper
     */
    public function get_mapper()
    {
        return $this->_mapper;
    }

    /**
     * Run the service
     */
    public function run(controller $controller)
    {
        switch ($this->_verb)
        {
            case 'get':
                // do not handle get
                break;
            case 'delete':
                $this->_handle_delete($controller);
                break;
            case 'post':
                $this->_handle_create($controller);
                break;
            case 'put':
                $this->_handle_update($controller);
                break;
        }
    }

    /**
     * Handle post request
     */
    private function _handle_create(controller $controller)
    {
        $received_data = $this->_get_properties();

        $child_controller = null;
        foreach ($controller->get_children() as $fieldname => $node)
        {
            if (!$node instanceof collection)
            {
                continue;
            }
            $child_controller = $node->get_controller();
            $parentfield = $this->_expand_property_name($node->get_attribute('rev'), $child_controller);
            if (!empty($received_data[$parentfield]))
            {
                $parent_identifier = trim($received_data[$parentfield][0], '<>');
                $parent = $this->_mapper->get_by_identifier($parent_identifier);
                $object = $this->_mapper->prepare_object($child_controller, $parent);
                $child_controller->set_object($object);
                return $this->_store_data($child_controller);
            }
        }
        $object = $this->_mapper->prepare_object($controller);
        $controller->set_object($object);
        return $this->_store_data($controller);
    }

    /**
     * Handle put request
     */
    private function _handle_update(controller $controller)
    {
        $object = $this->_mapper->get_by_identifier(trim($this->_data['@subject'], '<>'));
        $controller->set_object($object);
        return $this->_store_data($controller);
    }

    private function _store_data(controller $controller)
    {
        $new_values = $this->_get_properties();
        $object = $controller->get_object();

        foreach ($controller->get_children() as $fieldname => $node)
        {
            if (!$node instanceof propertyNode)
            {
                continue;
            }
            $rdf_name = $node->get_attribute('property');

            $expanded_name = $this->_expand_property_name($rdf_name, $controller);

            if (array_key_exists($expanded_name, $new_values))
            {
                $object = $this->_mapper->set_property_value($object, $node, $new_values[$expanded_name]);
            }
        }

        return $this->_mapper->store($object);
    }

    private function _expand_property_name($name, controller $controller)
    {
        $name = explode(":", $name);
        $vocabularies = $controller->get_vocabularies();
        return $vocabularies[$name[0]] . $name[1];
    }

    /**
     * Handle delete request
     */
    private function _handle_delete(controller $controller)
    {
        $object = $this->_mapper->get_by_identifier($_REQUEST["uri"]);
        return $this->_mapper->delete($object);
    }
}
?>
