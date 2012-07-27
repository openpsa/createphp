<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * This driver loads rdf mappings from xml files
 *
 * <type
 *      xmlns:sioc="http://rdfs.org/sioc/ns#"
 *      xmlns:dcterms="http://purl.org/dc/terms/"
 *      xmlns:skos="http://www.w3.org/2004/02/skos/core#"
 *      typeof="sioc:Post"
 * >
 *      <config key="my" value="value"/>
 *      <property property="dcterms:title" identifier="title" tag-name="h2"/>
 *      <collection rel="skos:related" identifier="tags" tag-name="ul"/>
 *      <property property="sioc:content" identifier="content" />
 * </type>
 *
 * @author David Buchmann <david@liip.ch>
 */
class RdfDriverXml implements RdfDriverInterface
{
    private $directories = array();

    /**
     * @param array $directories list of directories to look for rdf metadata
     */
    public function __construct($directories)
    {
        $this->directories = $directories;
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
        $xml = $this->getXmlDefinition($className);
        if (null == $xml) {
            return null;
        }

        $type = new Type($mapper, $this->getConfig($xml));

        foreach ($xml->getDocNamespaces(true) as $prefix => $uri) {
            $type->setVocabulary($prefix, $uri);
        }
        $type->setRdfType($xml['typeof']);
        foreach($xml->children() as $child) {
            switch($child->getName()) {
                case 'property':
                    $prop = new PropertyDefinition($child['identifier'], $this->getConfig($child));
                    $prop->setAttributes(array('property' => $child['property']));
                    if (isset($child['tag-name'])) {
                        $prop->setTagName($child['tag-name']);
                    }
                    $type->$child['identifier'] = $prop;
                    break;
                case 'collection':
                    $prop = new \Midgard\CreatePHP\Entity\Collection($child['identifier'], $this->getConfig($child));
                    // TODO? $prop->setAttributes(array('property' => $child['property']));
                    $prop->setAttributes(array('rel' => $child['rel']));
                    if (isset($child['tag-name'])) {
                        $prop->setTagName($child['tag-name']);
                    }
                    $type->$child['identifier'] = $prop;
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
    protected function getConfig(\SimpleXMLElement $xml)
    {
        $config = array();
        foreach ($xml->config as $c) {
            $config[(string)$c['key']] = (string)$c['value'];
        }
        return $config;
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
