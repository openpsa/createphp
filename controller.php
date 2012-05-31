<?php
/**
 * Abstract baseclass for the CreateController
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
class controller extends node
{
    /**
     * Flag that shows whether or not the object is editable
     *
     * @var boolean
     */
    private $_editable = true;

    /**
     * The mapper
     *
     * @var rdfMapper
     */
    protected $_mapper;

    /**
     * The current storage object, if any
     *
     * @var mixed
     */
    protected $_object;

    /**
     * Stores an array of rdf properties currently set for the controller
     *
     * @var array
     */
    protected $_properties = array();

    /**
     * The constructor
     *
     * @param rdfMapper $mapper
     * @param controller $parent the parent controller for collection children
     */
    public function __construct(rdfMapper $mapper, controller $parent = null)
    {
        $this->_mapper = $mapper;
        $this->_parent = $parent;
    }

    public function set_object($object, $schema_name)
    {
        $this->_object = $object;
        $this->_properties = array();
        $this->_mapper->get_config()->set_schema($schema_name);

        foreach ($this->_mapper->get_config()->get('attributes') as $key => $value)
        {
            $this->set_attribute($key, $value);
        }

        $map = $this->_mapper->get_config()->get('properties');

        // use rdf mapper to create element representations
        foreach ($map as $fieldname => $config)
        {
            $classname = array_shift($config['type']);
            $this->$fieldname = new $classname($config, $this);

            if ($this->$fieldname instanceof propertyNode)
            {
                if (empty($config['rdf_name']))
                {
                    $rdf_name = $this->_mapper->get_rdf_name($fieldname);
                }
                else
                {
                    $rdf_name = $config['rdf_name'];
                }
                $this->$fieldname->set_attribute('property', $rdf_name);
                $this->$fieldname->set_value($this->_mapper->get_property_value($object, $fieldname));
            }
        }

        $this->set_editable($this->_mapper->is_editable($object));
    }

    public function get_object()
    {
        return $this->_object;
    }

    /**
     * Magic getter
     *
     * @param string $key
     * @return node
     */
    public function __get($key)
    {
        if (isset($this->_properties[$key]))
        {
            return $this->_properties[$key];
        }
        return null;
    }

    /**
     * Magic setter
     *
     * @param string $key
     * @param node $node
     */
    public function __set($key, node $node)
    {
        $this->_properties[$key] = $node;
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

    public function set_editable($value)
    {
        $this->_editable = (bool) $value;
    }

    public function is_editable()
    {
        return $this->_editable;
    }

    /**
     * Renders the start tag
     *
     * @param string $tag_name
     * @return string
     */
    public function render_start($tag_name = false)
    {
        // render this for admin users only
        if ($this->is_editable())
        {
            // add about
            $this->set_attribute('about', $this->_mapper->create_identifier($this->_object));
        }

        // add xml namespaces
        foreach ($this->_mapper->get_vocabularies() as $prefix => $uri)
        {
            $this->set_attribute('xmlns:' . $prefix, $uri);
        }

        return parent::render_start($tag_name);
    }

    public function render_content()
    {
        $output = '';
        foreach ($this->_properties as $key => $prop)
        {
            $output .= $prop->render();
        }
        return $output;
    }
}
?>