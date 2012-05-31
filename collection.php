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

    public function __construct(array $settings, controller $parent)
    {
        $this->_config = $settings;

        foreach ($settings['attributes'] as $key => $value)
        {
            $this->set_attribute($key, $value);
        }

        $this->_controller = $parent;
        $parent_mapper = $this->_controller->get_mapper();
        $parent_object = $this->_controller->get_object();
        $mapper_class = get_class($parent_mapper);
        $config = $parent_mapper->get_config();
        $config->set_schema($settings['type'][0]);

        $children = $parent_mapper->get_children($parent_object, $config);

        if ($parent_mapper->is_editable($parent_object))
        {
            $object = $parent_mapper->prepare_object($config, $parent_object);
            array_unshift($children, $object);
        }

        // create controllers for children
        foreach ($children as $child)
        {
            $mapper = new $mapper_class($config);
            $controller = new controller($mapper, $this->_parent);
            $controller->set_object($child, $settings['type'][0]);
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
        $mapper = $this->_controller->get_mapper();
        // render this for admin users only
        if ($this->_controller->is_editable())
        {
            // add about
            $this->set_attribute('about', $mapper->create_identifier($this->_controller->get_object()));
        }

        // add xml namespaces
        foreach ($mapper->get_vocabularies() as $prefix => $uri)
        {
            $this->set_attribute('xmlns:' . $prefix, $uri);
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