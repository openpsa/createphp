<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Type\TypeInterface;

/**
 * The type holds information about a model class
 *
 * An entity is the actual instance of a type for a data entry
 */
interface EntityInterface extends TypeInterface, NodeInterface
{
    /**
     * Get the data object of this entity
     *
     * @return mixed
     */
    function getObject();

    /**
     * Whether this node is editable. This is initialized from what
     * RdfMapperInterface::isEditable tells, but can be overwritten with
     * setEditable.
     *
     * @return boolean
     */
    function isEditable();

    /**
     * Overwrite whether this node is editable.
     *
     * @param boolean $value
     */
    function setEditable($value);

    /**
     * Whether this entity is currently in process of being rendered.
     *
     * This is checked by the property when it is rendered to decide if it
     * should render the vocabulary as well.
     *
     * @return boolean
     */
    function isRendering();
}