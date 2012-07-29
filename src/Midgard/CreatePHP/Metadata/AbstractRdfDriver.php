<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;

/**
 * Base driver class with helper methods for drivers
 *
 * @author David Buchmann <david@liip.ch>
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
        if (!isset($child[$field])) {
            $property = self::DEFAULT_VOCABULARY_PREFIX . ':' . $identifier;
            $add_default_vocabulary = true;
        } else {
            $property = (string) $child[$field];
        }
        return $property;
    }
}
