<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\Type\CollectionDefinitionInterface;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\NodeInterface;

/**
 * Collection holder. Acts at the same time as a property to a parent entity and
 * and as a holder for entities of other objects which are linked to the first one
 * with some kind of relation
 *
 * @package Midgard.CreatePHP
 */
class Collection extends Node implements CollectionInterface
{
    /**
     * @var string
     */
    protected $_identifier;

    /**
     * @var RdfTypeFactory
     */
    protected $_typeFactory;

    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @var string
     */
    protected $_typename;

    /**
     * @param string $identifier the php property name used for this collection
     * @param RdfTypeFactory $typeFactory the typefactory to use with fixed child
     *      types
     * @param array $config application specific configuration to carry in this
     *      collection
     */
    public function __construct($identifier, RdfTypeFactory $typeFactory, array $config = array())
    {
        parent::__construct($config);
        $this->_identifier = $identifier;
        $this->_typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setRel($rel)
    {
        $this->setAttribute('rel', $rel);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getRel()
    {
        return $this->getAttribute('rel');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setRev($rev)
    {
        $this->setAttribute('rev', $rev);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getRev()
    {
        return $this->getAttribute('rev');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setTypeName($type)
    {
        $this->_typename = $type;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getType($vocabularies)
    {
        $expandedTypeName = $this->_expandPropertyName($this->_typename, $vocabularies);
        return $this->_typeFactory->getTypeByRdf($expandedTypeName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createWithParent(EntityInterface $parent)
    {
        $collection = clone $this;
        $collection->loadFromParent($parent);
        return $collection;
    }

    /**
     * Never call this method directly, but use createWithParent on your CollectionDefinition
     * to get a collection tied to concrete data.
     *
     * @private
     *
     * @param EntityInterface $parent
     */
    public function loadFromParent(EntityInterface $parent)
    {
        $this->_children = array();
        $object = $parent->getObject();
        $parentMapper = $parent->getMapper();

        $children = $parentMapper->getChildren($object, $this);

        // create entities for children
        foreach ($children as $child) {
            if (empty($this->_typename)) {
                $type = $this->_typeFactory->getType(get_class($child));
            } else {
                $expandedTypeName = $this->_expandPropertyName($this->_typename, $parent->getVocabularies());
                $type = $this->_typeFactory->getTypeByRdf($expandedTypeName);
            }

            foreach ($type->getVocabularies() as $prefix => $uri) {
                $this->setAttribute('xmlns:' . $prefix, $uri);
            }

            $this->_children[] = $type->createWithObject($child);
        }

        if ($this->_parent->isEditable($object) && sizeof($this->_children) == 0 && !empty($this->_typename)) {
            // create an empty element to allow adding new elements to an empty editable collection
            $type = $this->_typeFactory->getType($this->_typename);
            $mapper = $type->getMapper();
            $object = $mapper->prepareObject($type, $object);
            $entity = $type->createWithObject($object);
            if ($entity instanceof NodeInterface) {
                /** @var $entity NodeInterface */
                $entity->setAttribute('style', 'display:none');
            }
            $this->_children[] = $entity;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function renderStart($tag_name = false)
    {
        // render this for admin users only
        if (!$this->_parent->isEditable()) {
            $this->unsetAttribute('about');
        }

        return parent::renderStart($tag_name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function renderContent()
    {
        $ret = '';
        foreach ($this->_children as $child) {
            /** @var $child \Midgard\CreatePHP\Entity\NodeInterface */
            $ret .= $child->render();
        }
        return $ret;
    }


    /* ----- arrayaccess and iterator implementation methods ----- */
    function rewind()
    {
        $this->_position = 0;
    }

    function current()
    {
        return $this->_children[$this->_position];
    }

    function key()
    {
        return $this->_position;
    }

    function next()
    {
        ++$this->_position;
    }

    function valid()
    {
        return isset($this->_children[$this->_position]);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_children[] = $value;
        } else {
            $this->_children[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_children[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_children[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_children[$offset]) ? $this->_children[$offset] : null;
    }

    /**
     * Expand a property name to use full namespace instead of short name,
     * as used in reference fields.
     *
     * TODO: should this method go in a Helper class?
     *
     * @param string $name the name to expand, including namespace (e.g. sioc:Post)
     * @param array $vocabularies vocabulary to use for the expanding
     *
     * @return string the expanded name
     *
     * @throws \RuntimeException if the prefix is not in the vocabulary of
     *      $type
     */
    private function _expandPropertyName($name, $vocabularies)
    {
        $parts = explode(":", $name);
        if (!isset($vocabularies[$parts[0]])) {
            throw new \RuntimeException('Undefined namespace prefix \''.$parts[0]."' in '$name'");
        }
        return $vocabularies[$parts[0]] . $parts[1];
    }

}
