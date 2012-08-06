<?php
/**
 * Manager for the object controller types
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;

/**
 * A sort of micro service container to instantiate rdf type and entity as well
 * as the rest handler and workflows.
 *
 * @package Midgard.CreatePHP
 */
class Manager
{
    /**
     * Factory for types
     *
     * @var RdfTypeFactory
     */
    protected $_metadata;

    /**
     * Array of available workflows
     *
     * @var array of WorkflowInterface
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

    public function __construct(RdfMapperInterface $mapper, RdfTypeFactory $metadata)
    {
        $this->_mapper = $mapper;
        $this->_metadata = $metadata;
    }

    /**
     * Returns the type having this identifier
     *
     * @param string $identifier
     *
     * @return TypeInterface|null the type or null if not found
     */
    public function getType($identifier)
    {
        return $this->_metadata->getType($identifier);
    }

    /**
     * Get the bound entity for this object. The type is determined with
     * get_class(), unless you explicitly overwrite it by specifying the
     * identifier parameter.
     *
     * @param mixed $object your domain object to wrap into the rdf type
     * @param string $identifier optional identifier name to override the
     *      class name of your object.
     *
     * @return \Midgard\CreatePHP\Entity\EntityInterface|null the bound type of
     *      null if no type is found
     */
    public function getEntity($object, $identifier = null)
    {
        if (null == $identifier) {
            $identifier = get_class($object);
        }
        $type = $this->_metadata->getType($identifier);
        if (null == $type) {
            return null;
        }
        return $type->createWithObject($object);

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

    public function getRestHandler()
    {
        $restservice = new RestService($this->_mapper);
        foreach ($this->_workflows as $identifier => $workflow) {
            $restservice->setWorkflow($identifier, $workflow);
        }
        return $restservice;
    }

    /**
     * Register a workflow
     *
     * @param string $identifier
     * @param WorkflowInterface $Workflow
     */
    public function registerWorkflow($identifier, WorkflowInterface $workflow)
    {
        $this->_workflows[$identifier] = $workflow;
    }

    /**
     * Get all workflows available for this subject
     *
     * @param string $subject the RDFa identifier of the subject to get workflows for
     *
     * @return array of WorkflowInterface
     */
    public function getWorkflows($subject)
    {
        $response = array();
        $object = $this->_mapper->getBySubject(trim($subject, '<>'));
        foreach ($this->_workflows as $identifier => $workflow) {
            /** @var $workflow WorkflowInterface */
            $toolbar_config = $workflow->getToolbarConfig($object);
            if (null !== $toolbar_config) {
                $response[] = $toolbar_config;
            }
        }
        return $response;
    }
}
