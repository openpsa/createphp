<?php
/**
 * The type/entity implementation
 *
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\RdfElementDefinitionInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;
use Midgard\CreatePHP\Type\CollectionDefinitionInterface;

/**
 * @package Midgard.CreatePHP
 */
class Controller extends Node implements EntityInterface
{
    /**
     * Flag that shows whether or not the object is editable
     *
     * @var boolean
     */
    private $_editable = true;

    /**
     * The mapper
     *
     * @var RdfMapperInterface
     */
    protected $_mapper;

    /**
     * The vocabularies used in this instance
     *
     * @var array
     */
    protected $_vocabularies = array();

    /**
     * List of possible reverse mappings for this type
     *
     * @var array
     */
    protected $rev = array();

    /**
     * The current storage object, if any
     *
     * @var mixed
     */
    protected $_object;

    /**
     * The constructor
     *
     * @param RdfMapperInterface $mapper the mapper to use
     * @param array $config optional configuration values
     */
    public function __construct(RdfMapperInterface $mapper, array $config = array())
    {
        parent::__construct($config);
        $this->_mapper = $mapper;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getChildDefinitions()
    {
        return $this->getChildren();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createWithObject($object)
    {
        $entity = clone $this;
        $entity->setObject($object);
        return $entity;
    }

    /**
     * Internal method to map the object. Never call this method
     * but use createWithObject on the type object.
     *
     * @param mixed $object the application value to set
     *
     * @private
     */
    public function setObject($object)
    {
        $this->setEditable($this->_mapper->isEditable($object));
        $this->_object = $object;
        foreach ($this->_children as $name => $node) {
            $instance = null;
            if ($node instanceof PropertyDefinitionInterface) {
                /** @var $node PropertyDefinitionInterface */
                // the magic setter will also update the parent reference of the node
                $instance = $node->createWithValue($this->_mapper->getPropertyValue($object, $node));
            } elseif ($node instanceof CollectionDefinitionInterface) {
                /** @var $node CollectionDefinitionInterface */
                $instance = $node->createWithParent($this);
            } else {
                // we had a generic node in our tree. make sure the node gets its parent set.
                $instance = $node;
            }
            $this->$name = $instance;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setVocabulary($prefix, $uri)
    {
        $this->_vocabularies[$prefix] = $uri;
        $this->setAttribute('xmlns:' . $prefix, $uri);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getVocabularies()
    {
        return $this->_vocabularies;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setRdfType($type)
    {
        $this->setAttribute('typeof', $type);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getRdfType()
    {
        return $this->getAttribute('typeof');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function addRev($rev)
    {
        $this->rev[$rev] = $rev;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getRevOptions()
    {
        return $this->rev;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getObject()
    {
        return $this->_object;
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
        if ($this->_object && $this->isEditable()) {
            $this->setAttribute('about', $this->_mapper->createSubject($this->_object));
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function __get($key)
    {
        if (isset($this->_children[$key])) {
            return $this->_children[$key];
        }
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function __set($key, RdfElementDefinitionInterface $node)
    {
        $node->setParent($this);
        $this->_children[$key] = $node;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function __isset($key)
    {
        return isset($this->_children[$key]);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function setEditable($value)
    {
        $this->_editable = (bool) $value;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function isEditable()
    {
        return $this->_editable;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function renderStart($tag_name = false)
    {
        // render this for admin users only
        if (!$this->isEditable()) {
            // add about
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
        $output = '';
        foreach ($this->_children as $key => $prop) {
            /** @var $prop \Midgard\CreatePHP\NodeInterface */
            // add rdf name for admin only
            if (!$this->isEditable()) {
                $prop->unsetAttribute('property');
            }
            $output .= $prop->render();
        }
        return $output;
    }
}
