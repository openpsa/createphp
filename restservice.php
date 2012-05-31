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
        if (null !== $mapper)
        {
            $this->set_mapper($mapper);
        }
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
    public function run()
    {
        switch ($this->_verb)
        {
            case 'get':
                // do not handle get
                break;
            case 'delete':
                $this->_handle_delete();
                break;
            case 'post':
                $this->_handle_create();
                break;
            case 'put':
                $this->_handle_update();
                break;
        }
    }

    /**
     * Handle post request
     */
    private function _handle_create()
    {
        $map = $this->_mapper->get_config()->get('properties');
        $received_data = $this->_get_properties();

        $parent = null;
        foreach ($map as $fieldname => $config)
        {
            if (!isset($config['attributes']['rev']))
            {
                continue;
            }

            $parentfield = $this->_expand_property_name($config['attributes']['rev']);
            if (!empty($received_data[$parentfield]))
            {
                $parent_identifier = trim($received_data[$parentfield][0], '<>');
                $parent = $this->_mapper->get_by_identifier($parent_identifier);
                $this->_mapper->get_config()->set_schema($config['type'][1]);
            }
        }

        $object = $this->_mapper->prepare_object($this->_mapper->get_config(), $parent);
        return $this->_store_data($object);
    }

    /**
     * Handle put request
     */
    private function _handle_update()
    {
        $object = $this->_mapper->get_by_identifier(trim($this->_data['@subject'], '<>'));
        return $this->_store_data($object);
    }

    private function _store_data($object)
    {
        $new_values = $this->_get_properties();

        $properties = $this->_mapper->get_config()->get('properties');

        foreach ($properties as $fieldname => $config)
        {
            //TODO: Figure out a proper way to do this
            if (!empty($config['attributes']['rel']))
            {
                continue;
            }
            if (empty($config['rdf_name']))
            {
                $rdf_name = $this->_mapper->get_rdf_name($fieldname);
            }
            else
            {
                $rdf_name = $config['rdf_name'];
            }
            $expanded_name = $this->_expand_property_name($rdf_name);

            if (array_key_exists($expanded_name, $new_values))
            {
                $object = $this->_mapper->set_property_value($object, $fieldname, $new_values[$expanded_name]);
            }
        }

        return $this->_mapper->store($object);
    }

    private function _expand_property_name($name)
    {
        $name = explode(":", $name);
        $vocabularies = $this->_mapper->get_vocabularies();
        return $vocabularies[$name[0]] . $name[1];
    }

    /**
     * Handle delete request
     */
    private function _handle_delete()
    {
        $object = $this->_mapper->get_by_identifier($_REQUEST["uri"]);
        return $this->_mapper->delete($object);
    }
}
?>
