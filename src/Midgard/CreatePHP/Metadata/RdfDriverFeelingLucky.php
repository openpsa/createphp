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
class RdfDriverFeelingLucky implements RdfDriverInterface
{
    private $classNames = array();

    /**
     * {@inheritDoc}
     *
     * Build type from introspection of that class
     *
     * @api
     */
    public function loadTypeForClass($className, RdfMapperInterface $mapper, RdfTypeFactory $typeFactory)
    {
        if (! class_exists($className)) {
            return null;
        }

        $type = new Type($mapper);
        $type->setVocabulary('local', 'http://localhost/');
        $type->setAttribute('typeof', 'local:lucky');

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
                    $this->addProperty($type, lcfirst($candidate));
                }
            }
        }

        $fields = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($fields as $field) {
            $this->addProperty($type, $field->getName());
        }

        $this->classNames[$className] = $className;

        return $type;
    }

    private function addProperty(TypeInterface $type, $propName)
    {
// TODO: no cheating!
if ($propName != 'title' && $propName != 'content') return;
        $prop = new \Midgard\CreatePHP\Entity\Property(array(), $propName);
        $prop->setAttributes(array('property' => "local:$propName"));
        $type->$propName = $prop;
    }

    /**
     * Gets the names of all classes known to this driver.
     *
     * @return array The names of all classes known to this driver.
     */
    public function getAllClassNames()
    {
        return $this->classNames;
    }
}
