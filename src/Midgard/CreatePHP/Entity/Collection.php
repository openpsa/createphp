<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\Metadata\TypeNotFoundException;
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
     * List of type names this collection may contain
     * @var array
     */
    protected $_typenames = array();

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
        $this->_identifier = (string) $identifier;
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
        if (empty($this->_attributes['rev']) && count($this->_typenames) > 0) {
            $rev = null;
            foreach ($this->getTypes() as $child) {
                $revs = $child->getRevOptions();
                if (count($revs) !== 1) {
                    $type = $child->getRdfType();
                    throw new \Exception("Type $type in this collection does not specify exactly one possible rev attribute, please specify the rev attribute to use with this collection explicitly.");
                }
                if (null == $rev) {
                    $rev = reset($revs);
                    break;
                } elseif ($rev !== reset($revs)) {
                    $type = $child->getRdfType();
                    throw new \Exception("Type $type in this collection does not have the same rev attribute as the previous types, please fix your configuration.");
                }
            }

            $this->setRev($rev);
        }

        return $this->getAttribute('rev');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function addTypeName($type)
    {
        $this->_typenames[$type] = $type;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getTypes()
    {
        $types = array();
        foreach ($this->_typenames as $typename) {
            $types[$typename] = $this->_typeFactory->getTypeByRdf($typename);
            if (null == $types[$typename]) {
                throw new TypeNotFoundException($typename);
            }
        }
        return $types;
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
            if (count($this->_typenames) === 1) {
                $type = $this->_typeFactory->getTypeByRdf(current($this->_typenames));
            } else {
                $type = $this->_typeFactory->getTypeByObject($child);
            }

            foreach ($type->getVocabularies() as $prefix => $uri) {
                $this->setAttribute('xmlns:' . $prefix, $uri);
            }

            $this->_children[] = $type->createWithObject($child);
        }

        if ($this->_parent->isEditable($object) && sizeof($this->_children) == 0 && count($this->_typenames) == 1) {
            // create an empty element to allow adding new elements to an empty editable collection
            $type = $this->_typeFactory->getTypeByRdf(reset($this->_typenames));
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

        if (empty($this->_attributes['rev'])) {
            $this->getRev(); // trigger determining the rev attribute
        }

        return parent::renderStart($tag_name);
    }

    /**
     * {@inheritDoc}
     *
     * Overwrite to not output about attribute again if parent is rendering
     */
    public function renderAttributes(array $attributesToSkip = array())
    {
        if ($this->_parent && $this->_parent->isRendering()) {
            $attributesToSkip[] = 'about';
        }
        return parent::renderAttributes($attributesToSkip);
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
            /** @var $child NodeInterface */
            $ret .= $child->render();
        }
        return $ret;
    }

    public function getAttributes()
    {
        if (!isset($this->_attributes['about'])) {
            $this->ensureAbout();
        }

        return parent::getAttributes();
    }

    public function getAttribute($key)
    {
        if ('about' === $key && !isset($this->_attributes['about'])) {
            $this->ensureAbout();
        }

        return parent::getAttribute($key);
    }

    protected function ensureAbout()
    {
        if ($this->_parent instanceof NodeInterface
            && !$this->_parent->isRendering()
            && $this->_parent->isEditable()
        ) {
            $about = $this->_parent->getAttribute('about');
            if ($about) {
                $this->setAttribute('about', $about);
            }
        }
    }

    /* ----- arrayaccess and iterator implementation methods ----- */
    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_children[$this->_position];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
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
