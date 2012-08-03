<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\NodeInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;
use Midgard\CreatePHP\Type\CollectionDefinitionInterface;

/**
 * This driver is injected an array of rdf mapping definitions
 *
 * array(
 *      "My\\Model\\Class" => array (
 *          "vocabularies" => array(
 *              "sioc" => "http://rdfs.org/sioc/ns#",
 *              "dcterms" => "http://purl.org/dc/terms/",
 *          ),
 *          "typeof" => "sioc:Post",
 *          "config" => array(
 *              "key" => "value",
 *          ),
 *          "children" => array(
 *              "title" => array(
 *                  "type" => "property",
 *                  "property" => "dcterms:title",
 *                  "tag-name" => "h2",
 *              ),
 *              "tags" => array(
 *                  "type" => "collection",
 *                  "rel" => "skos:related",
 *                  "tag-name" => "ul",
 *                  "config" => array(
 *                      "key" => "value",
 *                  ),
 *              ),
 *              "content" => array(
 *                  "type" => "property",
 *                  "property" => "sioc:content",
 *              ),
 *          ),
 *      ),
 *  );
 *
 * @package Midgard.CreatePHP
 */
class RdfDriverArray extends AbstractRdfDriver
{
    private $definitions = array();

    /**
     * @param array $definitions array of type definitions
     */
    public function __construct($definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Return the NodeInterface wrapping a type for the specified class
     *
     * @param string $className
     * @param RdfMapperInterface $mapper
     *
     * @return \Midgard\CreatePHP\NodeInterface|null the type if found, otherwise null
     */
    function loadTypeForClass($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory)
    {
        if (! isset($this->definitions[$className])) {
            return null;
        }

        $definition = $this->definitions[$className];

        $typenode = $this->createType($mapper, $this->getConfig($definition));
        if (isset($definition['attributes'])) {
            $typenode->setAttributes($definition['attributes']);
        }
        if (isset($definition['tag-name'])) {
            $typenode->setTagName($definition['tag-name']);
        }

        /** @var $type TypeInterface */
        $type = $typenode->getRdfElement();

        if (isset($definition['vocabularies'])) {
            foreach ($definition['vocabularies'] as $prefix => $uri) {
                $type->setVocabulary($prefix, $uri);
            }
        }
        if (isset($definition['typeof'])) {
            $type->setRdfType($definition['typeof']);
        }
        $add_default_vocabulary = false;
        foreach($definition['children'] as $identifier => $child) {
            if (! isset($child['type'])) {
                $child['type'] = 'property';
            }
            $c = $this->createChild($child['type'], $identifier, $child, $typeFactory);
            $this->parseChild($c->getNode(), $child, $identifier, $add_default_vocabulary);
            if ($c instanceof CollectionDefinitionInterface && isset($child['controller'])) {
                $c->setType($child['controller']);
            }
            $type->$identifier = $c;
        }

        if ($add_default_vocabulary) {
            $type->setVocabulary(self::DEFAULT_VOCABULARY_PREFIX, self::DEFAULT_VOCABULARY_URI);
        }


        return $typenode;
    }

    /**
     * Build the attributes from the property|rel field and any custom attributes
     *
     * @param NodeInterface $propNode the node containing the property element
     * @param \ArrayAccess $child the child to read field from
     * @param string $field the field to be read, property for properties, rel for collections
     * @param string $identifier to be used in case there is no property field in $child
     * @param boolean $add_default_vocabulary flag to tell whether to add vocabulary for
     *      the default namespace.
     *
     * @return array properties
     */
    protected function parseChild(NodeInterface $propNode, $child, $identifier, &$add_default_vocabulary)
    {
        if ($propNode->getRdfElement() instanceof PropertyDefinitionInterface) {
            /** @var $prop PropertyDefinitionInterface */
            $prop = $propNode->getRdfElement();
            $prop->setProperty($this->buildInformation($child, $identifier, 'property', $add_default_vocabulary));
        } else {
            /** @var $col CollectionDefinitionInterface */
            $col = $propNode->getRdfElement();
            $col->setRel($this->buildInformation($child, $identifier, 'rel', $add_default_vocabulary));
            $col->setRev($this->buildInformation($child, $identifier, 'rev', $add_default_vocabulary));'rel';
        }

        if (isset($child['attributes'])) {
            $propNode->setAttributes($child['attributes']);
        }
        if (isset($child['tag-name'])) {
            $propNode->setTagName($child['tag-name']);
        }
    }

    /**
     * {@inheritDoc}
     *
     * For arrays, the node definition may have a 'config' child containing an
     * array. If it exists, we return that array.
     *
     * @param array $xml the element maybe having config children
     *
     * @return array the 'config' child of this node
     */
    protected function getConfig($node)
    {
        if (! isset($node['config'])) {
            return array();
        }
        return $node['config'];
    }

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    function getAllClassNames()
    {
        return array_keys($this->definitions);
    }
}
