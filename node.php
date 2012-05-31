<?php
/**
 * Encapsulates a node in the DOM tree
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
abstract class node
{
    /**
     * HTML element to use
     *
     * @var string
     */
    protected $_tag_name = 'div';

    /**
     * The element's attributes
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     * The element output template
     *
     * @var string
     */
    private $_template = "<__TAG_NAME__ __ATTRIBUTES__>__CONTENT__</__TAG_NAME__>\n";

    /**
     * The parent node
     *
     * @var node
     */
    protected $_parent;

    /**
     * The node's children, if any
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Adds an additional attribute to the wrapper tag
     *
     * @param string $key
     * @param string $value
     */
    public function set_attribute($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     * Adds an additional attribute to the wrapper tag
     *
     * @param string $key
     */
    public function get_attribute($key)
    {
        return $this->_attributes[$key];
    }

    /**
     * Sets the template
     *
     * @param string $template
     */
    public function set_template($template)
    {
        $this->_template = $template;
    }

    /**
     * Renders the element
     *
     * @param string $tag_name
     */
    public function render_start($tag_name = false)
    {
        if (is_string($tag_name))
        {
            $this->_tag_name = $tag_name;
        }

        $template = explode('__CONTENT__', $this->_template);
        $template = $template[0];

        $replace = array
        (
            "__TAG_NAME__" => $this->_tag_name,
            "__ATTRIBUTES__" => $this->render_attributes(),
        );

        return strtr($template, $replace);
    }

    /**
     * Renders everything including wrapper html tag and properties
     *
     * @param string $tag_name
     * @return string
     */
    public function render($tag_name = false)
    {
        $output = $this->render_start($tag_name);

        $output .= $this->render_content();

        $output .= $this->render_end();
        return $output;
    }

    public function render_attributes()
    {
        // add additional attributes
        $attributes = '';
        foreach ($this->_attributes as $key => $value)
        {
            $attributes .= ' ' . $key . '="' . $value . '"';
        }
        return $attributes;
    }

    abstract function render_content();

    public function render_end()
    {
        $template = explode('__CONTENT__', $this->_template);
        $template = $template[1];

        $replace = array
        (
            "__TAG_NAME__" => $this->_tag_name,
        );
        return strtr($template, $replace);
    }

    public function __toString()
    {
        return $this->render();
    }
}
?>