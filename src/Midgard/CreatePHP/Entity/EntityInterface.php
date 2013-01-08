<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Type\TypeInterface;

/**
 * The type holds information about a model class
 *
 * An entity is the actual instance of a type for a data entry
 *
 * @package Midgard.CreatePHP
 */
interface EntityInterface extends TypeInterface
{
    /**
     * Get the data object of this entity
     *
     * @return mixed
     */
    public function getObject();

    /**
     * Whether this node is editable. This is initialized from what
     * RdfMapperInterface::isEditable tells, but can be overwritten with
     * setEditable.
     *
     * @return boolean
     */
    public function isEditable();

    /**
     * Overwrite whether this node is editable.
     *
     * @param boolean $value
     */
    public function setEditable($value);

    /**
     * Whether this entity is currently in process of being rendered.
     *
     * This is checked by the property when it is rendered to decide if it
     * should render the vocabulary as well.
     *
     * @return boolean
     */
    public function isRendering();
}