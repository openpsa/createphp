<?php
/**
 * Abstract baseclass for rdfMapper
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
abstract class rdfMapper
{
    /**
     * the config array containing the mappings
     *
     * @var config
     */
    protected $_config;

    /**
     * The vocabularies used
     *
     * @var array
     */
    protected $_vocabularies = array();

    /**
     * Constructor
     *
     * @param config $config
     */
    public function __construct(config $config = null)
    {
        if (null === $config)
        {
            $config = new config;
        }
        $this->set_config($config);
    }

    /**
     * Config getter
     *
     * @return array
     */
    public function get_config()
    {
        return $this->_config;
    }

    /**
     * Config setter
     *
     * @param config $config
     */
    public function set_config(config $config)
    {
        $this->_config = $config;
    }

    /**
     * Register new namespace
     *
     * @param string $prefix
     * @param string $uri
     */
    public function register_vocabulary($prefix, $uri)
    {
        $this->_vocabularies[$prefix] = $uri;
    }

    /**
     * Get all namespaces
     *
     * @return array
     */
    public function get_vocabularies()
    {
        foreach ($this->_config->get('vocabularies') as $prefix => $uri)
        {
            $this->register_vocabulary($prefix, $uri);
        }
        return $this->_vocabularies;
    }

    /**
     * Set object property
     *
     * @param mixed $key
     * @param mixed $object
     * @param mixed $value
     * @return mixed
     */
    abstract function set_property_value($object, $key, $value);

    /**
     * Get object property
     *
     * @param mixed $key
     * @param mixed $object
     * @return mixed
     */
    abstract function get_property_value($object, $key);

    abstract function get_rdf_name($fieldname);

    abstract function is_editable($object);

    /**
     * Get object's children
     *
     * @param mixed $object
     * @param array $config
     * @return array
     */
    abstract function get_children($object, config $config);

    abstract function prepare_object(config $config);

    /**
     * Save object
     *
     * @param mixed $object
     */
    abstract function store($object);

    /**
     * Load object by identifier
     *
     * @param string $identifier
     * @return mixed The storage object or false if nothing is found
     */
    abstract function get_by_identifier($identifier);

    /**
     * Create identifier for passed object
     *
     * @param mixed $object
     * @return string
     */
    abstract function create_identifier($object);

    /**
     * Delete an object
     *
     * @param mixed $object
     */
    abstract function delete($object);
}
?>