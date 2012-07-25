<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

/**
 * The property definition holds information about an RDFa type attribute.
 *
 * @package Midgard.CreatePHP
 */
interface PropertyDefinitionInterface
{
    /**
     * Create a property from this definition and the concrete value
     *
     * @param string $value
     * @return \Midgard\CreatePHP\Entity\PropertyInterface
     */
    function createWithValue($value);

    /**
     * Get the identifier value of this property (RDFa attribute)
     *
     * @return string
     */
    function getIdentifier();

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
}
