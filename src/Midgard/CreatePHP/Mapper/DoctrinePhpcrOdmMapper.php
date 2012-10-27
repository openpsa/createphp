<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Mapper;

use Doctrine\Common\Persistence\ManagerRegistry;

use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\EntityInterface;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;

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
        if (null == $parent) {
            throw new \RuntimeException('You need a parent to create new objects');
        }

        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
        $meta = $this->om->getClassMetaData(get_class($object));
        $meta->setFieldValue($object, $meta->parentMapping, $parent);

        return $object;
    }

    /**
     * Overwrite to set the node name if not set
     *
     * @param EntityInterface $entity
     * @return bool|void
     */
    public function store(EntityInterface $entity)
    {
        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */

        $object = $entity->getObject();

        $meta = $this->om->getClassMetaData(get_class($object));

        $metaFieldValue = $meta->getFieldValue($object, $meta->node);

        if (empty($metaFieldValue)) {
            $title = $this->determineUrlTitle($entity);
            $meta->setFieldValue($object, $meta->nodename, $title);
        }

        return parent::store($entity);
    }

    /**
     * {@inheritDoc}
     */
    public function getBySubject($subject)
    {
        if (empty($subject)) {
            throw new \RuntimeException('Subject may not be empty');
        }
        $ret = $this->om->find(null, $subject);
        if (empty($ret)) throw new \RuntimeException("Not found: $subject");
        return $ret;
    }

    /**
     *
     * Determine the title of the object about to be stored, following this logic:
     *
     * 1. is there a getTitle method
     * 2. is there dcterms:title in rdf definition
     * 3. take the first property defined in rdf and take first 50 characters
     * 4. give up
     *
     * @param $entity
     */
    protected function determineTitle(EntityInterface $entity)
    {
        $object = $entity->getObject();

        //is there a getTitle method
        if (method_exists($object, 'getTitle')) {
            return $object->getTitle();
        } else {
            //try to get a dcterms:title in the rdf description
            foreach ($entity->getChildDefinitions() as $node) {
                if (!($node instanceof \Midgard\CreatePHP\Entity\PropertyInterface) ||
                    strpos($node->getProperty(), "dcterms:title") === false) {
                    continue;
                }
                return $entity->getMapper()->getPropertyValue($object, $node);
            }

            //try to get the first property to make a title
            foreach ($entity->getChildDefinitions() as $node) {
                if (!$node instanceof \Midgard\CreatePHP\Entity\PropertyInterface) {
                    continue;
                }
                return substr($entity->getMapper()->getPropertyValue($object, $node), 0, 49);
            }

            //give up
            throw new RuntimeException('No title could be found four your new node.');
        }
    }

    /**
     * Prepares an URL friendly title from an entity
     *
     * @param \Midgard\CreatePHP\Entity\EntityInterface $entity
     * @return mixed|string
     */
    protected function determineUrlTitle(EntityInterface $entity)
    {
        $title = $this->determineTitle($entity);

        //TODO: find a better way? For example with Doctrine_Inflector or Gedmo\Sluggable\Util\Urlizer
        setlocale(LC_CTYPE, 'en_US.UTF8');
        $title = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
        $title = trim($title, "\x00..\x1F");
        $title = preg_replace('/[^a-z0-9A-Z_.]/', '-', $title);

        return $title;
    }
}
