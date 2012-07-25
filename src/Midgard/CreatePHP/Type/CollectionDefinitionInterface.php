<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

use Iterator;
use ArrayAccess;
use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\Entity\EntityInterface;

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

    /**
     * Get the RDFa type for the items of this collection
     *
     * same TODO as above
     *
     * @return TypeInterface
     */
    function getType();

    /**
     * Get a html attribute.
     *
     * A collection must have rev attribute that names the attribute name that
     * child types use to point back to the parent. i.e. dcterms:partOf
     *
     * @param string $key
     *
     * @return string the value for this attribute or null if no such attribute
     */
    public function getAttribute($key);

    /**
     * Create a concrete collection from this definition with the children of the specified parent
     *
     * @param EntityInterface $parent
     *
     * @return \Midgard\CreatePHP\Entity\CollectionInterface
     */
    function createWithParent(EntityInterface $parent);

    /**
     * Set the tag name to use when rendering collections of this type
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
