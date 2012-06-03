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

    protected $_identifier;

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

    public function render_content()
    {
        return $this->get_value();
    }
}
?>