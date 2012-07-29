<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Type\CollectionDefinitionInterface;
use Midgard\CreatePHP\Type\TypeInterface;

/**
 * Collection holder. Acts at the same time as a property to a parent entity and
 * and as a holder for entities of other objects which are linked to the first one
 * with some kind of relation
 *
 * @package Midgard.CreatePHP
 */
class Collection extends Node implements CollectionInterface
{
    protected $_identifier;
    protected $_position = 0;

    /**
     * @var TypeInterface
     */
    protected $_type;

    /**
     * @param string $identifier the php property name used for this collection
     * @param array $config application specific configuration to carry in this
     *      collection
     */
    public function __construct($identifier, array $config = array())
    {
        $this->_identifier = $identifier;
        $this->_config = $config;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setType(TypeInterface $type)
    {
        $this->_type = $type;
        foreach ($type->getVocabularies() as $prefix => $uri) {
            $this->setAttribute('xmlns:' . $prefix, $uri);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getType()
    {
        return $this->_type;
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
        $config = $this->_type->getConfig();
        $children = $parentMapper->getChildren($object, $config);

        // create entities for children
        foreach ($children as $child) {
            $this->_children[] = $this->_type->createWithObject($child);
        }

        if ($this->_parent->isEditable($object) && sizeof($this->_children) == 0) {
            // create an empty element to allow adding new elements to an empty editable collection
            $mapper = $this->_type->getMapper();
            $object = $mapper->prepareObject($this->_type, $object);
            $entity = $this->_type->createWithObject($object);
            $entity->setAttribute('style', 'display:none');
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
            /** @var $child \Midgard\CreatePHP\NodeInterface */
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
}
