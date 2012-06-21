<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP\Entity;
use OpenPSA\CreatePHP\Node;
use ArrayAccess;
use Iterator;

/**
 * Collection holder. Acts at the same time as a property to a parent controller and
 * and as a holder for controllers of other objects which are linked to the first one
 * with some kind of relation
 *
 * @package OpenPSA.CreatePHP
 */
class Collection extends Node implements ArrayAccess, Iterator
{
    protected $_position = 0;

    protected $_controller;

    public function __construct(array $config, $identifier)
    {
        $this->_config = $config;
    }

    public function setController(Controller $controller)
    {
        $this->_controller = $controller;
        foreach ($controller->getVocabularies() as $prefix => $uri) {
            $this->setAttribute('xmlns:' . $prefix, $uri);
        }
    }

    public function getController()
    {
        return $this->_controller;
    }

    public function loadFromParent($object)
    {
        $this->_children = array();
        $mapper = $this->_controller->getMapper();
        $config = $this->_controller->getConfig();
        $children = $mapper->getChildren($object, $config);

        // create controllers for children
        foreach ($children as $child) {
            $controller = clone $this->_controller;
            $controller->setObject($child);
            $controller->setAttribute('about', $mapper->createIdentifier($child));
            $this->_children[] = $controller;
        }
        if ($this->_parent->isEditable($object) && sizeof($this->_children) == 0) {
            $object = $mapper->prepareObject($this->_controller, $object);
            $controller = clone $this->_controller;
            $controller->setObject($object);
            $controller->setAttribute('style', 'display:none');
            $this->_children[] = $controller;
        }
    }

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
     * Renders the start tag
     *
     * @param string $tag_name
     * @return string
     */
    public function renderStart($tag_name = false)
    {
        // render this for admin users only
        if (!$this->_parent->isEditable()) {
            $this->unsetAttribute('about');
        }

        return parent::renderStart($tag_name);
    }

    public function renderContent()
    {
        $ret = '';
        foreach ($this->_children as $child) {
            $ret .= $child->render();
        }
        return $ret;
    }
}
