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
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * The I am feeling lucky driver uses introspection to guess at fields of the
 * model class.
 *
 * It maps public properties and names that have both a setter and a getter.
 * This driver does not detect collections.
 *
 * This is not the recommended way to do the mapping, as you will end with
 * semantically useless RDFa mappings.
 *
 * @package Midgard.CreatePHP
 */
class RdfDriverFeelingLucky extends AbstractRdfDriver
{
    private $classNames = array();

    /**
     * {@inheritDoc}
     *
     * Build type from introspection of that class
     *
     * @api
     */
    public function loadType($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory)
    {
        if (! class_exists($className)) {
            throw new TypeNotFoundException('Class ' . $className . ' not found');
        }

        $typenode = $this->createType($mapper, array());
        /** @var $type TypeInterface */
        $type = $typenode->getRdfElement();

        $type->setVocabulary('lucky', 'http://localhost/lucky');
        $typeof = strtr($className, '\\', '_');
        $type->setRdfType('typeof', "lucky:$typeof");

        $class = new \ReflectionClass($className);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (! strncmp($method->getShortName(), 'set', 3)
                && strlen($method->getShortName()) > 3
            ) {
                $candidate = substr($method->getShortName(), 3);

                if ($class->hasMethod('get'.$candidate)
                    && $class->getMethod('set'.$candidate)->getNumberOfParameters() == 1
                    && $class->getMethod('get'.$candidate)->getNumberOfParameters() == 0
                ) {
                    // TODO: introspect if method is using array value => collection instead of property
                    $this->addProperty($typeFactory, $type, lcfirst($candidate));
                }
            }
        }

        $fields = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($fields as $field) {
            if (isset($type->{$field->getName()})) {
                // there was a getter and setter method for this
                continue;
            }
            $this->addProperty($typeFactory, $type, $field->getName());
        }

        $this->classNames[$className] = $className;

        return $typenode;
    }

    protected function getConfig($x)
    {
        return array();
    }

    private function addProperty(RdfTypeFactory $typeFactory, TypeInterface $type, $propName)
    {
        $prop = $this->createChild('property', $propName, null, $typeFactory);
        $prop->setProperty("lucky:$propName");
        $type->$propName = $prop;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllNames()
    {
        return $this->classNames;
    }

    protected function getAttributes($element)
    {
        throw new \Exception('this is never called');
    }

}
