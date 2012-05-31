<?php
/**
 * Encapsulates a property node in the DOM tree
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;

/**
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

    protected $_config;
    protected $_controller;

    public function __construct(array $config, controller $controller)
    {
        $this->_config = $config;
        $this->_controller = $controller;
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

    public function render_content()
    {
        return $this->get_value();
    }

    public function render($tag_name = false)
    {
        // add rdf name for admin only
        if (!$this->_controller->is_editable())
        {
            unset($this->_attributes['property']);
        }

        return parent::render($tag_name);
    }
}
?>