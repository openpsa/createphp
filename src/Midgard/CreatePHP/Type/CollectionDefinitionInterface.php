<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

use Midgard\CreatePHP\Node;
use ArrayAccess;
use Iterator;

/**
 * A collection is a special type of property that can hold a list of entities.
 *
 * @package Midgard.CreatePHP
 */
interface CollectionDefinitionInterface extends ArrayAccess, Iterator
{
    /**
     * Set the RDFa type for the items of this collection
     *
     * TODO: this limits the collection to one type - we should find a way to allow mixed collections
     *
     * @param TypeInterface $type
     */
    function setType(TypeInterface $type);
}
