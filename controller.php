<?php
/**
 * The type/object controller
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
     * The vocabularies used in this instance
     *
     * @var array
     */
    protected $_vocabularies = array();

    /**
     * The current storage object, if any
     *
     * @var mixed
     */
    protected $_object;

    /**
     * The constructor
     *
     * @param rdfMapper $mapper
     */
    public function __construct(rdfMapper $mapper, array $config = array())
    {
        $this->_mapper = $mapper;
        $this->_config = $config;
    }

    public function set_vocabulary($prefix, $uri)
    {
        $this->_vocabularies[$prefix] = $uri;
        $this->set_attribute('xmlns:' . $prefix, $uri);
    }

    public function get_vocabularies()
    {
        return $this->_vocabularies;
    }

    public function set_object($object)
    {
        $this->_object = $object;
        foreach ($this->_children as $fieldname => $node)
        {
            if ($node instanceof propertyNode)
            {
                $node->set_value($this->_mapper->get_property_value($object, $node));
            }
            else if ($node instanceof collection)
            {
                $node->set_attribute('about', $this->_mapper->create_identifier($object));
                $node->load_from_parent($object);
            }
        }
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
        if (isset($this->_children[$key]))
        {
            return $this->_children[$key];
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
        $this->_children[$key] = $node;
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
        if (!$this->is_editable())
        {
            // add about
            $this->unset_attribute('about');
        }

        return parent::render_start($tag_name);
    }

    public function render_content()
    {
        $output = '';
        foreach ($this->_children as $key => $prop)
        {
            // add rdf name for admin only
            if (!$this->is_editable())
            {
                $prop->unset_attribute('property');
            }
            $output .= $prop->render();
        }
        return $output;
    }

    public function __clone()
    {
        foreach ($this->_children as $name => $node)
        {
            $this->$name = clone $node;
            $this->$name->set_parent($this);
        }
    }
}
?>