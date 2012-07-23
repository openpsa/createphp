<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

use \Midgard\CreatePHP\Node;

/**
 * The type holds information about a model class
 *
 * An entity is the actual instance of a type for a data entry
 */
interface TypeInterface
{
    /**
     * Create an entity from this type and the application domain object.
     *
     * @return \Midgard\CreatePHP\Entity\EntityInterface the entity of this type bound to the supplied object
     */
    function createWithObject($object);

    /**
     * Config getter
     *
     * @return string
     */
    public function getConfig();

    /**
     * Set a prefix to an uri to build the namespace mapping
     *
     * @param $prefix
     * @param $uri
     */
    function setVocabulary($prefix, $uri);

    /**
     * Get a map of all vocabularies
     *
     * @return array of prefix => uri
     */
    function getVocabularies();

    /**
     * Magic getter
     *
     * @param string $key
     * @return Node|null
     */
    function __get($key);

    /**
     * Magic setter
     *
     * @param string $key
     * @param Node $node
     */
    function __set($key, Node $node);

    /**
     * Mapper getter
     *
     * @return \Midgard\CreatePHP\RdfMapperInterface
     */
    function getMapper();

    /**
     * Get all children definitions of this type
     *
     * @return array of PropertyDefinitionInterface|CollectionDefinitionInterface
     *      with the child definitions of this type
     */
    function getChildren();

    /**
     * Set the tag name to use when rendering entities of this type
     *
     * @param string $tag the html tag name without brackets
     */
    function setTagName($tag);
}