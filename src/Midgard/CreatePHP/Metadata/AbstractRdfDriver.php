<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;
use Midgard\CreatePHP\NodeInterface;

/**
 * Base driver class with helper methods for drivers
 *
 * @package Midgard.CreatePHP
 */
abstract class AbstractRdfDriver implements RdfDriverInterface
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
     * Build the property attribute from this child definition. The identifier is
     * used in case there is no property defined on the child - in which case the
     * $add_default_vocabulary flag is set to true. The caller has to set the
     * createphp vocabulary in that case.
     *
     * @param \ArrayAccess $child the child to read field from
     * @param string $field the field to be read, property for properties, rel for collections
     * @param string $identifier to be used in case there is no property field in $child
     * @param boolean $add_default_vocabulary flag to tell whether to add vocabulary for
     *      the default namespace.
     *
     * @return string property value
     */
    protected function buildInformation($child, $identifier, $field, &$add_default_vocabulary)
    {
        if (isset($child[$field])) {
            return (string) $child[$field];
        }
        switch($field) {
            case 'rel':
                return 'dcterms:hasPart';
            case 'rev':
                return 'dcterms:partOf';
            default:
                $add_default_vocabulary = true;
                return self::DEFAULT_VOCABULARY_PREFIX . ':' . $identifier;
        }
    }

    /**
     * Get the config information for this element
     *
     * @param mixed $element the configuration element representing any kind of node to read the config from
     *
     * @return array of key-value mappings for configuration
     */
    protected abstract function getConfig($element);

    /**
     * Get the attributes information for this element
     *
     * @param mixed $element the configuration element representing any kind of node to read the attributes from
     *
     * @return array of key-value mappings for attributes
     */
    protected abstract function getAttributes($element);

    /**
     * Create a type instance.
     *
     * @param RdfMapperInterface $mapper
     * @param array $config the configuration array to put into that type
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface
     */
    protected function createType(RdfMapperInterface $mapper, $config)
    {
        return new Type($mapper, $config);
    }

    /**
     * Instantiate a type model for this kind of child element
     *
     * @param string $kind the type information from the configuration
     * @param string $identifier the field name of this child
     * @param mixed $element the configuration element
     * @param RdfTypeFactory $typeFactory
     *
     * @return \Midgard\CreatePHP\Type\RdfElementDefinitionInterface
     *
     * @throws \Exception if $type is unknown
     */
    protected function createChild($kind, $identifier, $element, RdfTypeFactory $typeFactory)
    {
        switch($kind) {
            case 'property':
                $kind = new PropertyDefinition($identifier, $this->getConfig($element));
                break;
            case 'collection':
                $kind = new CollectionDefinition($identifier, $typeFactory, $this->getConfig($element));
                break;
            default:
                throw new \Exception('unknown kind of child '.$kind.' with identifier '.$identifier);
        }
        return $kind;
    }

    protected function parseNodeInfo(NodeInterface $node, $definition)
    {
        if ($attributes = $this->getAttributes($definition)) {
            $node->setAttributes($attributes);
        }
        if (isset($definition['tag-name'])) {
            $node->setTagName($definition['tag-name']);
        }
    }
}
