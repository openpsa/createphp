<?php
/**
 * Manager for the object controller types
 *
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
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
     * The mapper implementation to use
     *
     * @var RdfMapperInterface
     */
    protected $_mapper;

    /**
     * The RestService
     *
     * @var RestService
     */
    protected $_restService;


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
     * @return TypeInterface the type if found
     */
    public function getType($identifier)
    {
        return $this->_metadata->getTypeByName($identifier);
    }

    /**
     * Returns all loaded types
     *
     * @return array All loaded types
     */
    public function getLoadedTypes()
    {
        return $this->_metadata->getLoadedTypes();
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
        $type = $this->_metadata->getTypeByName($identifier);

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
        if (null === $this->_restService) {
            $this->_restService = new RestService($this->_mapper);
        }
        return $this->_restService;
    }
}
