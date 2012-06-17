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
use openpsa\createphp\entity\controller;

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
     * Array of available workflows
     *
     * @var array
     */
    protected $_workflows = array();

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
            $this->_controllers[$identifier]->set_editable($this->_mapper->is_editable($object));
            $this->_controllers[$identifier]->set_object($object);
            $this->_controllers[$identifier]->set_attribute('about', $this->_mapper->create_identifier($object));
        }
        return $this->_controllers[$identifier];
    }

    public function get_resthandler(array $received_data = null)
    {
        $restservice = new restservice($this->_mapper, $received_data);
        foreach ($this->_workflows as $identifier => $workflow)
        {
            $restservice->set_workflow($identifier, $workflow);
        }
        return $restservice;
    }

    /**
     * Register a workflow
     *
     * @param string $identifier
     * @param workflow $Workflow
     */
    public function register_workflow($identifier, workflow $workflow)
    {
        $this->_workflows[$identifier] = $workflow;
    }

    public function get_workflows($subject)
    {
        $response = array();
        $object = $this->_mapper->get_by_identifier(trim($subject, '<>'));
        foreach ($this->_workflows as $identifier => $workflow)
        {
            $toolbar_config = $workflow->get_toolbar_config($object);
            if (null !== $toolbar_config)
            {
                $response[] = $toolbar_config;
            }
        }
        return $response;
    }
}
?>