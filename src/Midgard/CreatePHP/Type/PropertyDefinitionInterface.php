<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

/**
 * The property definition holds information about an RDFa type attribute.
 *
 * @package Midgard.CreatePHP
 */
interface PropertyDefinitionInterface extends RdfElementDefinitionInterface
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
     * Set the property rdfa name
     *
     * @param string $property
     */
    function setProperty($property);

    /**
     * Get the property rdfa name
     *
     * @return string the rdf name of this property
     */
    function getProperty();

    /**
     * Containing type setter
     *
     * @param TypeInterface $parent The parent node
     */
    function setParentType(TypeInterface $parent);

    /**
     * Containing type getter
     *
     * @return TypeInterface The parent type
     */
    function getParentType();

}
