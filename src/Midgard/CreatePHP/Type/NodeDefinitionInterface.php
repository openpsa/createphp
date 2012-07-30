<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

/**
 * Common base interface for all type definitions
 *
 * @package Midgard.CreatePHP
 */

interface NodeDefinitionInterface
{
    /**
     * Parent node setter
     *
     * @param TypeInterface $parent The parent node
     */
    function setParent(TypeInterface $parent);

    /**
     * Parent node getter
     *
     * @return NodeDefinitionInterface The parent object (if any)
     */
    function getParent();

    /**
     * Children getter
     *
     * @return array of NodeDefinitionInterface (if any)
     */
    function getChildren();

    /**
     * Get client configuration array.
     *
     * This can be used to transport application specific information (table
     * names, mappings etc) with the type information. It is typically set
     * in the constructor of type classes.
     *
     * @return array with client configuration
     */
    function getConfig();

    /**
     * Set the tag name to use when rendering properties of this type
     *
     * @param string $tag the html tag name without brackets
     */
    function setTagName($tag);

    /**
     * Get the current tag name of this type
     *
     * @return string the tag name
     */
    function getTagName();

    /**
     * Sets the template used for rendering. The template must have the placeholders
     * __TAG_NAME__, __ATTRIBUTES__ and __CONTENT__
     *
     * @param string $template
     */
    function setTemplate($template);

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
     * Get a html attribute.
     *
     * Note that a collection must have rev attribute that names the attribute name that
     * child types use to point back to the parent. i.e. dcterms:partOf
     *
     * @param string $key
     *
     * @return string the value for this attribute or null if no such attribute
     */
    function getAttribute($key);

    /**
     * Get all html attributes, including the system ones like typeof, rev, property
     *
     * To render the attributes, you usually want to use NodeInterface::renderAttributes
     *
     * @return array of name => value
     */
    function getAttributes();

    /**
     * Remove an html attribute
     *
     * @param string $key
     */
    function unsetAttribute($key);

}