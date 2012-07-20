<?php
/**
 * The type/entity implementation
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\RdfMapperInterface;
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
     * The current storage object, if any
     *
     * @var mixed
     */
    protected $_object;

    /**
     * The constructor
     *
     * @param RdfMapperInterface $mapper
     */
    public function __construct(RdfMapperInterface $mapper, array $config = array())
    {
        $this->_mapper = $mapper;
        $this->_config = $config;
    }

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
                $instance->setAttribute('about', $this->_mapper->createIdentifier($object));
            } else {
                // we had a generic node in our tree. make sure the node gets its parent set.
                $instance = $node;
            }
            $this->$name = $instance;
        }

        $this->setAttribute('about', $this->_mapper->createIdentifier($object));
    }

    public function setVocabulary($prefix, $uri)
    {
        $this->_vocabularies[$prefix] = $uri;
        $this->setAttribute('xmlns:' . $prefix, $uri);
    }

    public function getVocabularies()
    {
        return $this->_vocabularies;
    }

    /**
     * Object getter
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Magic getter
     *
     * @param string $key
     * @return Node
     */
    public function __get($key)
    {
        if (isset($this->_children[$key])) {
            return $this->_children[$key];
        }
        return null;
    }

    /**
     * Magic setter
     *
     * @param string $key
     * @param Node $node
     */
    public function __set($key, Node $node)
    {
        $node->setParent($this);
        $this->_children[$key] = $node;
    }

    /**
     * Mapper getter
     *
     * @return RdfMapperInterface
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    public function setEditable($value)
    {
        $this->_editable = (bool) $value;
    }

    public function isEditable()
    {
        return $this->_editable;
    }

    /**
     * Renders the start tag
     *
     * @param string $tag_name
     * @return string
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
