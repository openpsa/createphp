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
    public function store($object)
    {
        $this->om->persist($object);
        $this->om->flush();
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * use getRealClass if className names a doctrine proxy class.
     */
    public function canonicalClassName($className)
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
}