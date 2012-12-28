<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Mapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Midgard\CreatePHP\Entity\EntityInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;

use PHPCR\ItemExistsException;

/**
 * Base mapper for doctrine, removing the proxy class names in canonicalClassName
 *
 * Extend and overwrite for your own doctrine based mapper.
 *
 * @package Midgard.CreatePHP
 */
abstract class BaseDoctrineRdfMapper extends AbstractRdfMapper
{
    /** @var ObjectManager */
    protected $om;

    /**
     * @param array $typeMap mapping of rdf type => className for prepareObject
     * @param ManagerRegistry $registry to get the document manager from
     * @param string|null $name the manager name, null will load the default manager
     */
    public function __construct($typeMap, ManagerRegistry $registry, $name = null)
    {
        parent::__construct($typeMap);
        $this->om = $registry->getManager($name);
    }

    /**
     * {@inheritDoc}
     *
     * Persist and flush (persisting an already managed document has no effect
     * and does not hurt).
     *
     * TODO: ensure that this has the right id resp. parent+name
     */
    public function store(EntityInterface $entity)
    {
        try {
            $this->om->persist($entity->getObject());
            $this->om->flush();
        } catch (ItemExistsException $iee) {
            //an item with the same title already exists
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * use getRealClass if className names a doctrine proxy class.
     */
    public function canonicalName($className)
    {
        $refl = new \ReflectionClass($className);
        if (in_array('Doctrine\\Common\\Persistence\\Proxy', $refl->getInterfaceNames())) {
            $className = \Doctrine\Common\Util\ClassUtils::getRealClass($className);
        }

        return $className;
    }

    /**
     * {@inheritDoc}
     *
     * Build the subject from all id's as per the metadata information,
     * concatenated with '-'.
     */
    public function createSubject($object)
    {
        $meta = $this->om->getClassMetaData(get_class($object));
        $ids = $meta->getIdentifierValues($object);
        return implode('-', $ids);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyValue($object, PropertyInterface $property)
    {
        $field = $this->getField($object, $property);

        $config = $property->getConfig();
        if (isset($config['doctrine:reference'])) {
            $meta = $this->om->getClassMetaData(get_class($object));
            $method = 'get' . ucfirst($meta->getIdentifier());
            return $object->$method();
        }

        return $field;
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyValue($object, PropertyInterface $property, $value)
    {
        $config = $property->getConfig();
        if (isset($config['doctrine:reference'])) {
            $value = $this->getBySubject($value);
        }
        return parent::setPropertyValue($object, $property, $value);
    }
}