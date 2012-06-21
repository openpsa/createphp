<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP\Entity;
use OpenPSA\CreatePHP\Node;

/**
 * Encapsulates a property node in the DOM tree.
 *
 * These nodes need the a "property" attribute to function correctly
 *
 * When rendering a property node separately, it will automatically render the controller
 * template as well, so that we have XML namespaces and about attributes required by the
 * JS interface
 *
 * @package OpenPSA.CreatePHP
 */
class Property extends Node
{
    /**
     * The element's content
     *
     * @var string
     */
    private $_value = '';

    /**
     * The property's identifier in the currently active controller
     *
     * @var string
     */
    protected $_identifier;

    /**
     * Flag that tracks whether or not the property is rendered as part of its
     * controller or standalone
     *
     * @var boolean
     */
    protected $_render_standalone = false;

    public function __construct(array $config, $identifier)
    {
        $this->_config = $config;
        $this->_identifier = $identifier;
    }

    /**
     * Sets the value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Value getter
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Identifier getter
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Render the property's opening tag (and the controller wrapper if we're in
     * standalone mode)
     */
    public function renderStart($tag_name = false)
    {
        $output = '';
        if (!$this->_parent->isRendering()) {
            $output .= $this->_parent->renderStart();
            $this->_render_standalone = true;
        }
        return $output . parent::renderStart($tag_name);
    }

    public function renderContent()
    {
        return $this->getValue();
    }

    /**
     * Render the property's closing tag (and the controller wrapper's if we're in
     * standalone mode)
     */
    public function renderEnd()
    {
        $output = parent::renderEnd();
        if ($this->_render_standalone) {
            $output .= $this->_parent->renderEnd();
            $this->_render_standalone = false;
        }
        return $output;
    }
}
