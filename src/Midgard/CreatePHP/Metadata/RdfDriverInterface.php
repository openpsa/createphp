<?php

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;

/**
 * Rdf information driver for createphp
 */
interface RdfDriverInterface
{
    const DEFAULT_VOCABULARY_URI = 'http://openpsa2.org/createphp/';
    const DEFAULT_VOCABULARY_PREFIX = 'createphp';

    /**
     * Return the type for the specified class
     *
     * @param string $className
     * @param RdfMapperInterface $mapper
     * @param RdfTypeFactory $typeFactory used to pass into collections
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface | null the type if found, otherwise null
     */
    function loadTypeForClass($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory);

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    function getAllClassNames();
}
