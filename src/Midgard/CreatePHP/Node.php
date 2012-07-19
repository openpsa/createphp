<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

/**
 * Baseclass for (DOM) nodes.
 *
 * Provides functionality for managing relevant aspects of the node, specifically, managing
 * attributes, parent/children relations and rendering. The latter is split into three
 * different functions for maximum flexibility. So you can call render() to output the
 * complete node HTML, or you can call render_start() for the opening tag, render_content()
 * for the node's content (or children) and render_end() for the colsing tag.
 *
 * @package Midgard.CreatePHP
 */
abstract class Node
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
     * Additional config parameters passed to the node
     *
     * @var array
     */
    protected $_config;

    /**
     * Flag that tracks whether or not we're between render_start() and render_end()
     *
     * @var boolean
     */
    protected $_is_rendering = false;

    /**
     * Parent node setter
     *
     * @var node $parent The parent object
     */
    public function setParent(Node $parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Parent node getter
     *
     * @return node The parent object (if any)
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Children getter
     *
     * @return array The child nodes (if any)
     */
    public function getChildren()
    {
        return $this->_children;
    }

    public function isRendering()
    {
        return $this->_is_rendering;
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Adds an additional attribute
     *
     * @param string $key
     * @param string $value
     */
    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     * Sets multiple attributes at once
     *
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Get an attribute
     *
     * @param string $key
     */
    public function getAttribute($key)
    {
        if (!isset($this->_attributes[$key])) {
            return null;
        }
        return $this->_attributes[$key];
    }

    /**
     * Remove an attribute
     *
     * @param string $key
     */
    public function unsetAttribute($key)
    {
        if (isset($this->_attributes[$key])) {
            unset($this->_attributes[$key]);
        }
    }

    /**
     * Sets the template
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Renders the element
     *
     * @param string $tag_name
     */
    public function renderStart($tag_name = false)
    {
        if (is_string($tag_name)) {
            $this->_tag_name = $tag_name;
        }

        $template = explode('__CONTENT__', $this->_template);
        $template = $template[0];

        $replace = array
        (
            "__TAG_NAME__" => $this->_tag_name,
            " __ATTRIBUTES__" => $this->renderAttributes(),
        );
        $this->_is_rendering = true;
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
        $output = $this->renderStart($tag_name);

        $output .= $this->renderContent();

        $output .= $this->renderEnd();
        return $output;
    }

    public function renderAttributes()
    {
        // add additional attributes
        $attributes = '';
        foreach ($this->_attributes as $key => $value) {
            $attributes .= ' ' . $key . '="' . $value . '"';
        }
        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }
        return $attributes;
    }

    abstract function renderContent();

    public function renderEnd()
    {
        $template = explode('__CONTENT__', $this->_template);
        $template = $template[1];

        $replace = array
        (
            "__TAG_NAME__" => $this->_tag_name,
        );
        $this->_is_rendering = false;
        return strtr($template, $replace);
    }

    public function __toString()
    {
        return $this->render();
    }
}
