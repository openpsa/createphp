<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\TypeInterface;

/**
 * Baseclass for (DOM) nodes.
 *
 * Provides functionality for managing relevant aspects of the node, specifically, managing
 * attributes, parent/children relations and rendering. The latter is split into three
 * different functions for maximum flexibility. So you can call render() to output the
 * complete node HTML, or you can call render_start() for the opening tag, render_content()
 * for the node's content (or children) and render_end() for the closing tag.
 *
 * @package Midgard.CreatePHP
 */
abstract class Node implements NodeInterface
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
     * @var \Midgard\CreatePHP\Entity\EntityInterface
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

    public function __construct($config)
    {
        $this->_config = $config;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setParent(NodeInterface $parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Set the parent type
     *
     * @param TypeInterface $type
     */
    public function setParentType(TypeInterface $type)
    {
        $this->setParent($type);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Get the parent type
     *
     * @return TypeInterface
     */
    public function getParentType()
    {
        return $this->getParent();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Check if the node is currently in its output phase
     *
     * @return boolean
     */
    public function isRendering()
    {
        return $this->_is_rendering;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAttribute($key)
    {
        if (!isset($this->_attributes[$key])) {
            return null;
        }
        return $this->_attributes[$key];
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function unsetAttribute($key)
    {
        if (isset($this->_attributes[$key])) {
            unset($this->_attributes[$key]);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setTagName($tag)
    {
        $this->_tag_name = $tag;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getTagName()
    {
        return $this->_tag_name;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function renderStart($tag_name = false)
    {
        if (is_string($tag_name)) {
            $this->_tag_name = $tag_name;
        }

        $attributesToSkip = array();

        if (   $this->_parent
            && $this->_parent->isRendering())
        {
            //remove about to work around a VIE bug with nested identical about attributes
            $attributesToSkip[] = 'about';
        }

        $template = explode('__CONTENT__', $this->_template);
        $template = $template[0];

        $replace = array
        (
            "__TAG_NAME__" => $this->_tag_name,
            " __ATTRIBUTES__" => $this->renderAttributes($attributesToSkip),
        );

        $this->_is_rendering = true;
        return strtr($template, $replace);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function render($tag_name = false)
    {
        $output = '';
        /**
         * Do not render start and end again if the node is already rendering.
         */
        $rendering = $this->isRendering();
        if (! $rendering) {
            $output = $this->renderStart($tag_name);
        }

        $output .= $this->renderContent();

        if (! $rendering) {
            $output .= $this->renderEnd();
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function renderAttributes(array $attributesToSkip = array())
    {
        // add additional attributes
        $attributes = '';
        $swappedAttributes = array_flip($attributesToSkip);
        foreach ($this->_attributes as $key => $value) {
            if (isset($swappedAttributes[$key])) {
                continue;
            }
            $attributes .= ' ' . $key . '="' . $value . '"';
        }
        if ($attributes === ' ') {
            $attributes = '';
        }
        return $attributes;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
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

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function __toString()
    {
        return $this->render();
    }
}
