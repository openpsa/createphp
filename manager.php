<?php
/**
 * Manager for the object controller types
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
class manager
{
    /**
     * Array of available controller types
     *
     * @var array
     */
    protected $_controllers = array();

    /**
     * The mapper implementation to use
     *
     * @var rdfMapper
     */
    protected $_mapper;

    public function __construct(rdfMapper $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Set a controller
     *
     * @param string $identifier
     * @param controller $controller
     */
    public function set_controller($identifier, controller $controller)
    {
        $this->_controllers[$identifier] = $controller;
    }

    /**
     * Get a controller
     *
     * @param string $identifier
     * @param mixed $object
     * @return controller
     */
    public function get_controller($identifier, $object = null)
    {
        if (!isset($this->_controllers[$identifier]))
        {
            return null;
        }
        if (null !== $object)
        {
            $this->_controllers[$identifier]->set_object($object);
            $this->_controllers[$identifier]->set_editable($this->_mapper->is_editable($object));
            $this->_controllers[$identifier]->set_attribute('about', $this->_mapper->create_identifier($object));
        }
        return $this->_controllers[$identifier];
    }
}
?>