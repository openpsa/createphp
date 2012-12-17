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
use Midgard\CreatePHP\Helper\NamespaceHelper;

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
 *          "rev" => array("dcterms:partOf"),
 *          "config" => array(
 *              "key" => "value",
 *          ),
 *          "children" => array(
 *              "title" => array(
 *                  "nodeType" => "property",
 *                  "property" => "dcterms:title",
 *                  "tag-name" => "h2",
 *                  "editor" => "someConfiguredEditor"
 *              ),
 *              "tags" => array(
 *                  "nodeType" => "collection",
 *                  "rel" => "skos:related",
 *                  "tag-name" => "ul",
 *                  "config" => array(
 *                      "key" => "value",
 *                  ),
 *              ),
 *              "content" => array(
 *                  "nodeType" => "property",
 *                  "property" => "sioc:content",
 *              ),
 *              "children" => array(
 *                  "nodeType" => "collection",
 *                  "rel" => "skos:related",
 *                  "tag-name" => "ul",
 *                  "childtypes" => array("sioc:Post"),
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
     * @return \Midgard\CreatePHP\Type\TypeInterface the type if found
     * @throws \Midgard\CreatePHP\Metadata\TypeNotFoundException
     */
    public function loadType($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory)
    {
        if (! isset($this->definitions[$className])) {
            throw new TypeNotFoundException('No definition found for ' . $className);
        }

        $definition = $this->definitions[$className];

        $type = $this->createType($mapper, $this->getConfig($definition));
        if ($type instanceof NodeInterface) {
            $this->parseNodeInfo($type, $definition);
        }

        if (isset($definition['vocabularies'])) {
            foreach ($definition['vocabularies'] as $prefix => $uri) {
                $type->setVocabulary($prefix, $uri);
            }
        }

        if (isset($definition['rev'])) {
            foreach ($definition['rev'] as $rev) {
                $type->addRev((string) $rev);
            }
        }

        if (isset($definition['typeof'])) {
            $type->setRdfType($definition['typeof']);
        }
        $add_default_vocabulary = false;
        foreach ($definition['children'] as $identifier => $child) {
            if (! isset($child['nodeType'])) {
                $child['nodeType'] = 'property';
            }
            $c = $this->createChild($child['nodeType'], $identifier, $child, $typeFactory);
            $this->parseChild($c, $child, $identifier, $type, $add_default_vocabulary);
            $type->$identifier = $c;
        }

        if ($add_default_vocabulary) {
            $type->setVocabulary(self::DEFAULT_VOCABULARY_PREFIX, self::DEFAULT_VOCABULARY_URI);
        }

        return $type;
    }

    /**
     * Build the attributes from the property|rel field and any custom attributes
     *
     * @param mixed $child the child element to parse
     * @param \ArrayAccess $childData the child to read field from
     * @param string $field the field to be read, property for properties, rel for collections
     * @param string $identifier to be used in case there is no property field in $child
     * @param TypeInterface $parentType the parent object, i.e. for namespaces
     * @param boolean $add_default_vocabulary flag to tell whether to add vocabulary for
     *      the default namespace.
     *
     * @return array properties
     */
    protected function parseChild($child, $childData, $identifier, TypeInterface $parentType, &$add_default_vocabulary)
    {
        if ($child instanceof PropertyDefinitionInterface) {
            /** @var $child PropertyDefinitionInterface */
            $child->setProperty($this->buildInformation($childData, $identifier, 'property', $add_default_vocabulary));
        } elseif ($child instanceof CollectionDefinitionInterface) {
            /** @var $child CollectionDefinitionInterface */
            $child->setRel($this->buildInformation($childData, $identifier, 'rel', $add_default_vocabulary));
            $child->setRev($this->buildInformation($childData, $identifier, 'rev', $add_default_vocabulary));
            if (isset($childData['childtypes'])) {
                foreach ($childData['childtypes'] as $type) {
                    $expanded = NamespaceHelper::expandNamespace($type, $parentType->getVocabularies());
                    $child->addTypeName($expanded);
                }
            }
        }

        if ($child instanceof NodeInterface) {
            $this->parseNodeInfo($child, $childData);
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
    protected function getAttributes($node)
    {
        if (! isset($node['attributes'])) {
            return array();
        }
        return $node['attributes'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAllNames()
    {
        $map = array();
        foreach ($this->definitions as $name => $definition) {
            $type = $name;
            if (isset($definition['typeof'])) {
                $type = $definition['typeof'];
                if (isset($definition['vocabularies'])) {
                    $type = NamespaceHelper::expandNamespace($definition['typeof'], $definition['vocabularies']);
                }
            }

            $map[$type] = $name;
        }

        return $map;
    }
}
