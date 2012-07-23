<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * This driver loads rdf mappings from xml files
 *
 * <type
 *      xmlns:sioc="http://rdfs.org/sioc/ns#"
 *      xmlns:dcterms="http://purl.org/dc/terms/"
 *      typeof="sioc:Post"
 * >
 *     <property property="dcterms:title" identifier="title" tag-name="h2"/>
 *     <property property="sioc:content" identifier="content" />
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

        $type = new Type($mapper);

        foreach ($xml->getDocNamespaces(true) as $prefix => $uri) {
            $type->setVocabulary($prefix, $uri);
        }
        $type->setAttribute('typeof', $xml['typeof']);
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
