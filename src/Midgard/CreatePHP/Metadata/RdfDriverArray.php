<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;

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
class RdfDriverArray implements RdfDriverInterface
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
    function loadTypeForClass($className, RdfMapperInterface $mapper)
    {
        if (! isset($this->definitions[$className])) {
            return null;
        }

        $definition = $this->definitions[$className];
        $type = new Type($mapper, $this->getConfig($definition));

        foreach ($definition['vocabularies'] as $prefix => $uri) {
            $type->setVocabulary($prefix, $uri);
        }
        $type->setRdfType($definition['typeof']);
        foreach($definition['children'] as $identifier => $child) {
            switch($child['type']) {
                case 'property':
                    $prop = new PropertyDefinition($identifier, $this->getConfig($child));
                    $prop->setAttributes(array('property' => $child['property']));
                    if (isset($child['tag-name'])) {
                        $prop->setTagName($child['tag-name']);
                    }
                    $type->$identifier = $prop;
                    break;
                case 'collection':
                    $col = new CollectionDefinition($identifier, $this->getConfig($child));
                    $col->setAttributes(array('rel' => $child['rel']));
                    if (isset($child['tag-name'])) {
                        $col->setTagName($child['tag-name']);
                    }
                    $type->$identifier = $col;
                    break;
            }
        }

        return $type;
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
