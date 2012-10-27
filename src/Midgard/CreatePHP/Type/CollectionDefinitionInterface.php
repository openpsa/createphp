<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

use Iterator;
use ArrayAccess;
use Midgard\CreatePHP\Entity\EntityInterface;

/**
 * A collection is a special type of property that can hold a list of entities.
 *
 * @package Midgard.CreatePHP
 */
interface CollectionDefinitionInterface extends ArrayAccess, Iterator, RdfElementDefinitionInterface
{
    /**
     * Set an overriding RDFa type for the items of this collection
     *
     * If this is not set, the collection will use the RdfTypeFactory to find
     * types for the bound data based on their classes.
     *
     * @param string $typename the argument to be used with RdfTypeFactory::getType
     */
    public function setTypeName($typename);

    /**
     * Get the overriding RDFa type for the items of this collection
     *
     * @return TypeInterface
     */
    public function getType();

    /**
     * Set the reverse link of this collection (typically dcterms:partOf)
     *
     * @param string $rev
     */
    public function setRev($rev);

    /**
     * Get the reverse link of this collection (typically dcterms:partOf)
     *
     * @return string reverse link
     */
    public function getRev();

    /**
     * Set the related link of this collection (typically dcterms:hasPart)
     *
     * @param string $rel
     */
    public function setRel($rel);

    /**
     * Get the related link of this collection (typically dcterms:hasPart)
     *
     * @return string the related name
     */
    public function getRel();

    /**
     * Create a concrete collection from this definition with the children of the specified parent
     *
     * @param EntityInterface $parent
     *
     * @return \Midgard\CreatePHP\Entity\CollectionInterface
     */
    public function createWithParent(EntityInterface $parent);

    /**
     * Get the identifier value of this property (RDFa attribute)
     *
     * @return string
     */
    public function getIdentifier();

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
