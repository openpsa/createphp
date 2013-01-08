<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Type\PropertyDefinitionInterface;

/**
 * Encapsulates a RDFa attribute in the DOM tree.
 *
 * These nodes need a "property" attribute to function correctly
 *
 * When rendering a property node separately, it will automatically render the entity
 * template as well, so that we have XML namespaces and about attributes as required by the
 * JS interface
 *
 * @package Midgard.CreatePHP
 */
interface PropertyInterface extends PropertyDefinitionInterface
{
    /**
     * Get the value of this property
     *
     * @return string
     */
    public function getValue();

    /**
     * Change the value of this property
     *
     * @param string $value
     */
    public function setValue($value);

    /**
     * Get the editor of this property
     *
     * @return string
     */
    public function getEditor();

    /**
     * Change the editor of this property
     *
     * @param string $editor
     */
    public function setEditor($editor);
}
