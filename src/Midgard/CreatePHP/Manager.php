<?php
/**
 * Manager for the object controller types
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;
use Midgard\CreatePHP\Entity\Controller;

/**
 * @package Midgard.CreatePHP
 */
class Manager
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
     * @var RdfMapperInterface
     */
    protected $_mapper;

    /**
     * The widget (JS constructor) to use
     *
     * @var Widget
     */
    protected $_widget;

    public function __construct(RdfMapperInterface $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Set a controller
     *
     * @param string $identifier
     * @param Controller $controller
     */
    public function setController($identifier, Controller $controller)
    {
        $this->_controllers[$identifier] = $controller;
    }

    /**
     * Get a controller
     *
     * @param string $identifier
     * @param mixed $object
     * @return Controller
     */
    public function getController($identifier, $object = null)
    {
        if (!isset($this->_controllers[$identifier])) {
            return null;
        }
        if (null !== $object) {
            $this->_controllers[$identifier]->setObject($object);
        }
        return $this->_controllers[$identifier];
    }

    /**
     * Widget setter
     *
     * @param widget $widget
     */
    public function setWidget(Widget $widget)
    {
        $this->_widget = $widget;
    }

    /**
     * Widget getter
     *
     * @return widget
     */
    public function getWidget()
    {
        return $this->_widget;
    }

    public function getRestHandler(array $received_data = null)
    {
        $restservice = new RestService($this->_mapper, $received_data);
        foreach ($this->_workflows as $identifier => $workflow) {
            $restservice->setWorkflow($identifier, $workflow);
        }
        return $restservice;
    }

    /**
     * Register a workflow
     *
     * @param string $identifier
     * @param Workflow $Workflow
     */
    public function registerWorkflow($identifier, Workflow $workflow)
    {
        $this->_workflows[$identifier] = $workflow;
    }

    public function getWorkflows($subject)
    {
        $response = array();
        $object = $this->_mapper->getByIdentifier(trim($subject, '<>'));
        foreach ($this->_workflows as $identifier => $workflow) {
            $toolbar_config = $workflow->getToolbarConfig($object);
            if (null !== $toolbar_config) {
                $response[] = $toolbar_config;
            }
        }
        return $response;
    }
}
