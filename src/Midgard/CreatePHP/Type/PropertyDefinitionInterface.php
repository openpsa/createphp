<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
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
    public function createWithValue($value);

    /**
     * Get the identifier value of this property (RDFa attribute)
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set the property rdfa name
     *
     * @param string $property
     */
    public function setProperty($property);

    /**
     * Get the property rdfa name
     *
     * @return string the rdf name of this property
     */
    public function getProperty();

    /**
     * Containing type setter
     *
     * @param TypeInterface $parent The parent node
     */
    public function setParentType(TypeInterface $parent);

    /**
     * Containing type getter
     *
     * @return TypeInterface The parent type
     */
    public function getParentType();

}
