<?php

namespace Liip\VieBundle\Mapper;

use Doctrine\Common\Persistence\ManagerRegistry;

use Midgard\CreatePHP\BaseDoctrineRdfMapper;
use Midgard\CreatePHP\Type\TypeInterface;


/**
 * Mapper to handle PHPCR-ODM.
 *
 * For orm, this will be more difficult as the subject will need to carry
 * the information of which entity it is about.
 */
class DoctrinePhpcrOdmMapper extends BaseDoctrineRdfMapper
{
    public function prepareObject(TypeInterface $type, $parent = null)
    {
        $object = parent::prepareObject($type);
        if (null !== $parent) {
            /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
            $meta = $this->om->getClassMetaData(get_class($object));
            $meta->setFieldValue($object, $meta->parentMapping, $parent);
        }
        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getBySubject($subject) {
        if (empty($subject)) {
            throw new \RuntimeException('Subject may not be empty');
        }
        $ret = $this->om->find(null, $subject);
        if (empty($ret)) throw new \RuntimeException("Not found: $subject");
        return $ret;
    }

}