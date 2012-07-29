<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;

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
 * @author David Buchmann <david@liip.ch>
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
     * Return the type for the specified class
     *
     * @param string $className
     * @param RdfMapperInterface $mapper
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface|null the type if found, otherwise null
     */
    function loadTypeForClass($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory)
    {
        if (! isset($this->definitions[$className])) {
            return null;
        }

        $definition = $this->definitions[$className];
        $type = new Type($mapper, $this->getConfig($definition));

        if (isset($definition['vocabularies'])) {
            foreach ($definition['vocabularies'] as $prefix => $uri) {
                $type->setVocabulary($prefix, $uri);
            }
        }
        if (isset($definition['attributes'])) {
            $type->setAttributes($definition['attributes']);
        }
        if (isset($definition['typeof'])) {
            $type->setRdfType($definition['typeof']);
        }
        $add_default_vocabulary = false;
        foreach($definition['children'] as $identifier => $child) {
            if (! isset($child['type'])) {
                throw new \Exception("Child $identifier is missing the type key");
            }
            switch($child['type']) {
                case 'property':
                    $prop = new PropertyDefinition($identifier, $this->getConfig($child));
                    $this->parseChild($prop, $child, $identifier, $add_default_vocabulary);
                    $type->$identifier = $prop;
                    break;
                case 'collection':
                    $col = new CollectionDefinition($identifier, $typeFactory, $this->getConfig($child));
                    $this->parseChild($col, $child, $identifier, $add_default_vocabulary);
                    if (isset($child['controller'])) {
                        $col->setType($child['controller']);
                    }
                    $type->$identifier = $col;
                    break;
            }
        }


        if (!empty($config['vocabularies'])) {
            foreach ($config['vocabularies'] as $prefix => $uri) {
                $type->setVocabulary($prefix, $uri);
            }
        }
        if ($add_default_vocabulary) {
            $type->setVocabulary(self::DEFAULT_VOCABULARY_PREFIX, self::DEFAULT_VOCABULARY_URI);
        }


        return $type;
    }

    /**
     * Build the attributes from the property|rel field and any custom attributes
     *
     * @param \ArrayAccess $child the child to read field from
     * @param string $field the field to be read, property for properties, rel for collections
     * @param string $identifier to be used in case there is no property field in $child
     * @param boolean $add_default_vocabulary flag to tell whether to add vocabulary for
     *      the default namespace.
     *
     * @return array properties
     */
    protected function parseChild($prop, $child, $identifier, &$add_default_vocabulary)
    {
        $type = $prop instanceof PropertyDefinitionInterface ? 'property' : 'rel';
        $attributes = array(
            $type => $this->buildInformation($child, $identifier, $type, $add_default_vocabulary)
        );
        if (isset($child['attributes'])) {
            $attributes = array_merge($child['attributes'], $attributes);
        }
        $prop->setAttributes($attributes);
        if (isset($child['tag-name'])) {
            $prop->setTagName($child['tag-name']);
        }
    }

    /**
     * Get the configuration from <config key="x" value="y"/> elements.
     *
     * @param \SimpleXMLElement $xml the element maybe having config children
     *
     * @return array built from the config children of the element
     */
    protected function getConfig(array $node)
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
