<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;

/**
 * Encapsulates a property node in the DOM tree.
 *
 * These nodes need the a "property" attribute to function correctly
 *
 * When rendering a property node separately, it will automatically render the controller
 * template as well, so that we have XML namespaces and about attributes required by the
 * JS interface
 *
 * @package openpsa.createphp
 */
class propertyNode extends node
{
    /**
     * the element's content
     *
     * @var string
     */
    private $_value = '';

    protected $_identifier;

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
    public function set_value($value)
    {
        $this->_value = $value;
    }

    /**
     * Value getter
     *
     * @return string
     */
    public function get_value()
    {
        return $this->_value;
    }

    /**
     * Identifier getter
     *
     * @return string
     */
    public function get_identifier()
    {
        return $this->_identifier;
    }

    public function render_start($tag_name = false)
    {
        $output = '';
        if (!$this->_parent->is_rendering())
        {
            $output .= $this->_parent->render_start();
            $this->_render_standalone = true;
        }
        return $output . parent::render_start($tag_name);
    }

    public function render_content()
    {
        return $this->get_value();
    }

    public function render_end()
    {
        $output = parent::render_end();
        if ($this->_render_standalone)
        {
            $output .= $this->_parent->render_end();
            $this->_render_standalone = false;
        }
        return $output;
    }
}
?>