<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Entity\EntityInterface;

/**
 * Base interface for (DOM) nodes.
 *
 * Provides functionality for managing relevant aspects of the node, specifically, managing
 * attributes, parent/children relations and rendering. The latter is split into three
 * different functions for maximum flexibility. So you can call render() to output the
 * complete node HTML, or you can call render_start() for the opening tag, render_content()
 * for the node's content (or children) and render_end() for the closing tag.
 *
 * @package Midgard.CreatePHP
 */
interface NodeInterface
{
    /**
     * Parent node setter
     *
     * @param EntityInterface $parent The parent node
     */
    function setParent(EntityInterface $parent);

    /**
     * Parent node getter
     *
     * @return NodeInterface The parent object (if any)
     */
    function getParent();

    /**
     * Children getter
     *
     * @return array of NodeInterface (if any)
     */
    function getChildren();

    /**
     * Adds or overwrites an html attribute
     *
     * @param string $key
     * @param string $value
     */
    function setAttribute($key, $value);

    /**
     * Sets the attributes in the passed array, keeping
     * attributes not mentioned in the array.
     *
     * @param array $attributes key => value
     */
    function setAttributes($attributes);

    /**
     * Get a html attribute
     *
     * @param string $key
     *
     * @return string the value for this attribute or null if no such attribute
     */
    public function getAttribute($key);

    /**
     * Remove an html attribute
     *
     * @param string $key
     */
    public function unsetAttribute($key);

    /**
     * Sets the template used for rendering. The template must have the placeholders
     * __TAG_NAME__, __ATTRIBUTES__ and __CONTENT__
     *
     * @param string $template
     */
    public function setTemplate($template);

    /**
     * Renders everything including wrapper html tag and properties
     *
     * If you want more control over the generated HTML, call renderStart,
     * renderContent and renderEnd separately
     *
     * @param string $tag_name
     *
     * @return string the rendered html
     */
    public function render($tag_name = false);

    /**
     * Renders introduction part for this node
     *
     * @param string $tag_name set to a string to overwrite what html tag should be written
     *
     * @return string the rendered html
     */
    function renderStart($tag_name = false);

    /**
     * Render the content of this node, including its children if applicable
     *
     * @return string the rendered html
     */
    function renderContent();

    /**
     * Render tail part for this node
     *
     * @return string the rendered html
     */
    public function renderEnd();

    /**
     * Render just the attributes. This is not needed if you use
     * renderStart()
     *
     * @return string the rendered attributes
     */
    function renderAttributes();

    /**
     * Has to return the same as self::render()
     *
     * @return string
     */
    public function __toString();
}
