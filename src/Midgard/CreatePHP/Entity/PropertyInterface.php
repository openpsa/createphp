<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\NodeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;

/**
 * Encapsulates a RDFa attribute in the DOM tree.
 *
 * These nodes need a "property" attribute to function correctly
 *
 * When rendering a property node separately, it will automatically render the controller
 * template as well, so that we have XML namespaces and about attributes as required by the
 * JS interface
 *
 * @package Midgard.CreatePHP
 */
interface PropertyInterface extends NodeInterface, PropertyDefinitionInterface
{
    /**
     * Get the value of this property
     *
     * @return string
     */
    function getValue();
}
