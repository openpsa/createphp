<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * This driver is injected an array of rdf mapping definitions
 *
 * array(
 *      "My\\Model\\Class" => array (
 *          "vocabularies" => array(
 *              "xmlns:sioc" => "http://rdfs.org/sioc/ns#",
 *              "xmlns:dcterms" => "http://purl.org/dc/terms/",
 *          ),
 *          "typeof" => "sioc:Post",
 *          "properties => array(
 *              "property" => array(
 *                  "property" => "dcterms:title",
 *                  "identifier" => "title",
 *                  "tag-name" => "h2",
 *              ),
 *              "property" => array(
 *                  "property" => "sioc:content",
 *                  "identifier" => "content",
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

        // TODO: build from array

        $type = new Type($mapper);

        foreach ($xml->getDocNamespaces(true) as $prefix => $uri) {
            $type->setVocabulary($prefix, $uri);
        }
        foreach($xml->property as $property) {
            $prop = new \Midgard\CreatePHP\Entity\Property(array(), $property['identifier']);
            $prop->setAttributes(array('property' => $property['property']));
            if (isset($property['tag-name'])) {
                $prop->setTagName($property['tag-name']);
            }
            $type->$property['identifier'] = $prop;
        }

        return $type;
    }

    /**
     * @param $className
     * @return \SimpleXMLElement
     */
    protected  function getXmlDefinition($className)
    {
        $filename = $this->buildFileName($className);
        foreach ($this->directories as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $filename)) {
                return simplexml_load_file($dir . DIRECTORY_SEPARATOR . $filename);
            }
        }
        return null;
    }

    protected function buildFileName($className)
    {
        return str_replace('\\', '.', $className) . '.xml';
    }

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    function getAllClassNames()
    {
        //TODO
    }
}
