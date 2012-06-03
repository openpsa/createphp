<?php
/**
 * Collection holder
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
class collection extends node implements \ArrayAccess, \Iterator
{
    protected $_position = 0;

    protected $_config;

    protected $_controller;

    public function __construct(array $settings, $identifier)
    {
        $this->_config = $settings;

        foreach ($settings['attributes'] as $key => $value)
        {
            $this->set_attribute($key, $value);
        }
    }

    public function set_controller(controller $controller)
    {
        $this->_controller = $controller;
        foreach ($controller->get_vocabularies() as $prefix => $uri)
        {
            $this->set_attribute('xmlns:' . $prefix, $uri);
        }
    }

    public function get_controller()
    {
        return $this->_controller;
    }

    public function load_from_parent($object)
    {
        $this->_children = array();
        $mapper = $this->_controller->get_mapper();
        $config = $this->_controller->get_config();
        $children = $mapper->get_children($object, $config);

        if ($this->_parent->is_editable($object))
        {
            $object = $mapper->prepare_object($this->_controller, $object);
            array_unshift($children, $object);
        }

        // create controllers for children
        foreach ($children as $child)
        {
            $controller = clone $this->_controller;
            $controller->set_object($child);
            $controller->set_attribute('about', $mapper->create_identifier($child));
            $this->_children[] = $controller;
        }
    }

    function rewind()
    {
        $this->_position = 0;
    }

    function current()
    {
        return $this->_children[$this->_position];
    }

    function key()
    {
        return $this->_position;
    }

    function next()
    {
        ++$this->_position;
    }

    function valid()
    {
        return isset($this->_children[$this->_position]);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->_children[] = $value;
        }
        else
        {
            $this->_children[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_children[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_children[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_children[$offset]) ? $this->_children[$offset] : null;
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
        if (!$this->_parent->is_editable())
        {
            $this->unset_attribute('about');
        }

        return parent::render_start($tag_name);
    }

    public function render_content()
    {
        $ret = '';
        foreach ($this->_children as $child)
        {
            $ret .= $child->render();
        }
        return $ret;
    }
}
?>