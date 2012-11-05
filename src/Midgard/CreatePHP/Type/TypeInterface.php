<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Type;

/**
 * The type holds information about a model class.
 *
 * An entity is the actual instance of a type for a data entry.
 */
interface TypeInterface extends RdfElementDefinitionInterface
{
    /**
     * Create an entity from this type and the application domain object.
     *
     * @return \Midgard\CreatePHP\Entity\EntityInterface the entity of this type bound to the supplied object
     */
    public function createWithObject($object);

    /**
     * Set a prefix to an uri to build the namespace mapping
     *
     * @param $prefix
     * @param $uri
     */
    public function setVocabulary($prefix, $uri);

    /**
     * Get a map of all vocabularies
     *
     * @return array of prefix => uri
     */
    public function getVocabularies();

    /**
     * Set the rdf type of this type, i.e. sioc:Post
     *
     * @param string $type the namespaced rdf type
     */
    public function setRdfType($type);

    /**
     * Get the rdf type string of this type
     *
     * @return string
     */
    public function getRdfType();

    /**
     * Get the child node at this key
     *
     * @param string $key
     * @return RdfElementDefinitionInterface|null
     */
    public function __get($key);

    /**
     * Set child node with this key
     *
     * @param string $key
     * @param RdfElementDefinitionInterface $node
     */
    public function __set($key, RdfElementDefinitionInterface $node);

    /**
     * Check if child with this key exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function __isset($key);

    /**
     * Mapper getter
     *
     * @return \Midgard\CreatePHP\RdfMapperInterface
     */
    public function getMapper();

    /**
     * Get all children definitions of this type
     *
     * @return array of PropertyDefinitionInterface|CollectionDefinitionInterface
     *      with the child definitions of this type
     */
    public function getChildDefinitions();
}