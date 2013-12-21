<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Mapper;

use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Entity\EntityInterface;

use PHPCR\ItemExistsException;

use \RuntimeException;

/**
 * Mapper to handle PHPCR-ODM.
 */
class DoctrinePhpcrOdmMapper extends BaseDoctrineRdfMapper
{

    /**
     * {@inheritDoc}
     */
    public function prepareObject(TypeInterface $type, $parent = null)
    {
        $object = parent::prepareObject($type);
        if (null == $parent) {
            throw new RuntimeException('You need a parent to create new objects');
        }

        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
        $meta = $this->om->getClassMetaData(get_class($object));

        if (!property_exists($object, $meta->parentMapping)) {
            throw new RuntimeException('parentMapping need to be mapped to '
                . get_class($object));
        }

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
        $meta = $this->om->getClassMetaData(get_class($entity->getObject()));

        if (!property_exists($entity->getObject(), $meta->nodename)) {
            throw new RuntimeException('nodename need to be mapped to '
                . get_class($entity->getObject()));
        }

        $nodename = $meta->getFieldValue($entity->getObject(), $meta->nodename);

        if (empty($nodename)) { //in case of node creation the nodename is empty
            $name = $this->generateNodeName($entity);
            //set a guessed nodename
            $meta->setFieldValue($entity->getObject(), $meta->nodename, $name);
        }

        try {
            return parent::store($entity);
        } catch (ItemExistsException $iee) {
            //an item with the same title already exists
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * With PHPCR-ODM we simply use the full repository path as id.
     */
    public function createSubject($object)
    {
        return $this->om->getUnitOfWork()->getDocumentId($object);
    }

    /**
     * {@inheritDoc}
     */
    public function getBySubject($subject)
    {
        if (empty($subject)) {
            throw new RuntimeException('Subject may not be empty');
        }
        $ret = $this->om->find(null, $subject);
        if (empty($ret)) throw new RuntimeException("Not found: $subject");
        return $ret;
    }

    /**
     * Generate an URL friendly node name from an entity
     * Find a usable property and remove spaces, accents and special chars
     *
     * @param EntityInterface $entity
     * @return mixed|string
     */
    protected function generateNodeName(EntityInterface $entity)
    {
        $title = $this->determineEntityTitle($entity);

        //TODO: find a better way? For example with Doctrine_Inflector or Gedmo\Sluggable\Util\Urlizer
        setlocale(LC_CTYPE, 'en_US.UTF8');
        $title = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
        $title = trim($title, "\x00..\x1F");
        $title = preg_replace('/[^a-z0-9A-Z_.]/', '-', $title);

        return $title;
    }

    /**
     *
     * Determine the title of the entity to be created, following this logic:
     *
     * 1. is there a getTitle method with a value set
     * 2. is there a property containing "title" in rdf definition
     * 3. take the first property defined in rdf and take first 50 characters
     * 4. give up
     *
     * @param EntityInterface $entity
     */
    private function determineEntityTitle(EntityInterface $entity)
    {
        $object = $entity->getObject();

        //is there a getTitle method?
        if (method_exists($object, 'getTitle') && $object->getTitle()) {
            return $object->getTitle();
        } else {
            //try to get a property containing title in the rdf description
            foreach ($entity->getChildDefinitions() as $node) {
                if (!($node instanceof \Midgard\CreatePHP\Entity\PropertyInterface) ||
                    strpos($node->getProperty(), "title") === false) {
                    continue;
                }
                return $entity->getMapper()->getPropertyValue($object, $node);
            }

            //try to get the first property to create a guessed title of max 50 characters
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
     * Reorder the children of the collection node according to the expected order
     *
     * @param EntityInterface $entity
     * @param CollectionInterface $node
     * @param $expectedOrder array of subjects
     */
    public function orderChildren(EntityInterface $entity, CollectionInterface $node, $expectedOrder)
    {
        array_walk($expectedOrder, array($this, 'stripParentPath'));
        $childrenCollection = $this->getChildren($entity->getObject(), $node);
        $children = $childrenCollection->toArray();
        $childrenNames = array_keys($children);

        $childrenNames = $this->sort($childrenNames, $expectedOrder);

        $childrenCollection->clear();

        foreach ($childrenNames as $name) {
            $childrenCollection->set($name, $children[$name]);
        }
    }

    public function stripParentPath(&$item, $key)
    {
        $item = basename($item);
    }
    
    /**
     * stable sort is not implemented in php, so we need to sort ourself
     */
    public function sort($array, $reference)
    {
        $headIdx = 0;
        $tailIdx = 0;
        $i = 0;
        foreach($array as $element) {
            $i++;
            if (false === array_search($element, $reference)) {
                if (0 == $tailIdx) {
                    $headIdx = $i;
                }
            } else {
                $tailIdx = $i;
            }
        }

        $toSort = array_splice($array, $headIdx);
        $tail = array_splice($toSort, $tailIdx - $headIdx);

        for ($i=1; $i < count($toSort); $i++) {
            $tempIdx = (int)array_search($toSort[$i], $reference);
            $temp = $toSort[$i];
            $j = $i - 1;

            while ($j >= 0 && (int)array_search($toSort[$j], $reference) > $tempIdx){
                $toSort[$j + 1] = $toSort[$j];
                $j--;
            }

            $toSort[$j+1] = $temp;
        }

        return array_merge($array, $toSort, $tail);
    }

}
