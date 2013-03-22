<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

/**
 * This is a dom-node like class to handle rendering rdf elements
 *
 * @package Midgard.CreatePHP
 */

interface NodeInterface
{
    /**
     * Parent node setter
     *
     * @param NodeInterface $parent The parent node
     */
    public function setParent(NodeInterface $parent);

    /**
     * Parent node getter
     *
     * @return NodeInterface The parent object (if any)
     */
    public function getParent();

    /**
     * Children getter
     *
     * @return array of NodeInterface (if any)
     */
    public function getChildren();

    /**
     * Set the tag name to use when rendering properties of this type
     *
     * @param string $tag the html tag name without brackets
     */
    public function setTagName($tag);

    /**
     * Get the current tag name of this type
     *
     * @return string the tag name
     */
    public function getTagName();

    /**
     * Sets the template used for rendering. The template must have the placeholders
     * __TAG_NAME__, __ATTRIBUTES__ and __CONTENT__
     *
     * @param string $template
     */
    public function setTemplate($template);

    /**
     * Adds or overwrites an html attribute
     *
     * @param string $key
     * @param string $value
     */
    public function setAttribute($key, $value);

    /**
     * Sets the attributes in the passed array, keeping
     * attributes not mentioned in the array.
     *
     * @param array $attributes key => value
     */
    public function setAttributes($attributes);

    /**
     * Get a html attribute.
     *
     * Note that a collection must have rev attribute that names the attribute name that
     * child types use to point back to the parent. i.e. dcterms:partOf
     *
     * @param string $key
     *
     * @return string the value for this attribute or null if no such attribute
     */
    public function getAttribute($key);

    /**
     * Get all html attributes, including the system ones like typeof, rev, property
     *
     * To render the attributes, you usually want to use NodeInterface::renderAttributes
     *
     * @return array of name => value
     */
    public function getAttributes();

    /**
     * Remove an html attribute
     *
     * @param string $key
     */
    public function unsetAttribute($key);

    /**
     * Renders everything needed.
     *
     * If you want more control over the generated HTML, call renderStart,
     * renderContent and renderEnd separately
     *
     * If called after renderStart but before renderEnd, it will just do
     * renderContent to not duplicate the wrapper html tag and properties
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
    public function renderStart($tag_name = false);

    /**
     * Render the content of this node, including its children if applicable
     *
     * @return string the rendered html
     */
    public function renderContent();

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
     * @param array $attributesToSkip attributes names that will not be printed
     *
     * @return string the rendered attributes
     */
    public function renderAttributes(array $attributesToSkip = array());

    /**
     * Has to return the same as self::render()
     *
     * @return string
     */
    public function __toString();
}