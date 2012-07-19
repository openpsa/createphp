<?php
/**
 * The type/object controller
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;
use Midgard\CreatePHP\Node;
use Midgard\CreatePHP\RdfMapperInterface;

/**
 * @package Midgard.CreatePHP
 */
class Controller extends Node
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
     * Object setter. This connects both the controller and the derived elements,
     * i.e. collections and properties
     *
     * @param mixed $object the storage "object"
     */
    public function setObject($object)
    {
        $this->_object = $object;
        foreach ($this->_children as $fieldname => $node) {
            if ($node instanceof Property) {
                $node->setValue($this->_mapper->getPropertyValue($object, $node));
            } elseif ($node instanceof Collection) {
                $node->setAttribute('about', $this->_mapper->createIdentifier($object));
                $node->loadFromParent($object);
            }
        }
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
            // add rdf name for admin only
            if (!$this->isEditable()) {
                $prop->unsetAttribute('property');
            }
            $output .= $prop->render();
        }
        return $output;
    }

    public function __clone()
    {
        foreach ($this->_children as $name => $node)
        {
            $this->$name = clone $node;
            $this->$name->setParent($this);
        }
    }
}
