<?php
/**
 * REST service backend
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\CollectionDefinitionInterface;
use Midgard\CreatePHP\Type\TypeInterface;

use Midgard\CreatePHP\Entity\EntityInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;

/**
 * @package Midgard.CreatePHP
 */
class RestService
{
    const HTTP_GET = 'get';
    const HTTP_POST = 'post';
    const HTTP_PUT = 'put';
    const HTTP_DELETE = 'delete';

    /**
     * The mapper object
     *
     * @var RdfMapperInterface
     */
    protected $_mapper;

    /**
     * The passed data, if any
     *
     * @var array
     */
    protected $_data;

    /**
     * Custom workflows for post, put, delete or get
     *
     * @var array
     */
    protected $_workflows = array();

    /**
     * The constructor
     *
     * @param RdfMapperInterface $mapper
     */
    public function __construct(RdfMapperInterface $mapper, array $data = null)
    {
        $this->_data = $data;
        $this->setMapper($mapper);
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get transmitted properties
     *
     * @return array
     */
    private function _getProperties()
    {
        $return = array();

        foreach ($this->_data as $key => $value) {
            if (substr($key, 0, 1) === '@') {
                continue;
            }
            $key = trim($key, '<>');
            $return[$key] = $value;
        }
        return $return;
    }

    /**
     * You can overwrite the handling for the basic http methods get, put, post
     * and delete here.
     *
     * Note that for delete you need to register a workflow to have it happen.
     *
     * @param string $identifier one of the HTTP constants of this class
     * @param WorkflowInterface $workflow
     */
    public function setWorkflow($identifier, WorkflowInterface $workflow)
    {
        $this->_workflows[$identifier] = $workflow;
    }

    /**
     * Mapper setter
     *
     * @param RdfMapperInterface $mapper
     */
    public function setMapper(RdfMapperInterface $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Mapper getter
     *
     * @return RdfMapperInterface
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * Execute the rest operation
     *
     * @param TypeInterface $entity the bound entity with data to process
     * @param string $method the http request method, one of the HTTP constants,
     *      if omitted, $_SERVER['REQUEST_METHOD'] is used
     *
     * @return null|array if this is a successful post or put, returns the json
     *      data for the processed item
     */
    public function run(TypeInterface $type, $method = null)
    {
        if (null === $method) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }

        if (array_key_exists($method, $this->_workflows)) {
            $object = null;
            if (isset($_GET["subject"])) {
                $object = $this->_mapper->getBySubject($_GET["subject"]);
            }
            return $this->_workflows[$method]->run($object);
        }

        switch ($method) {
            case self::HTTP_GET:
                // do not handle get
                return null;
            case self::HTTP_DELETE:
                //delete is a workflow, so it's not handled here directly
                return null;
            case self::HTTP_POST:
                return $this->_handleCreate($type);
            case self::HTTP_PUT:
                return $this->_handleUpdate($type);
            default:
                throw new \UnexpectedValueException("No workflow found to handle $method");
        }
    }

    /**
     * Handle post request
     */
    private function _handleCreate(TypeInterface $type)
    {
        $received_data = $this->_getProperties();

        foreach ($type->getChildren() as $fieldname => $node) {
            if (!$node instanceof CollectionDefinitionInterface) {
                continue;
            }
            /** @var $node CollectionDefinitionInterface */
            $child_type = $node->getType();
            $parentfield = $this->_expandPropertyName($node->getAttribute('rev'), $child_type);
            if (!empty($received_data[$parentfield])) {
                $parent_identifier = trim($received_data[$parentfield][0], '<>');
                $parent = $this->_mapper->getBySubject($parent_identifier);
                $object = $this->_mapper->prepareObject($child_type, $parent);
                $entity = $child_type->createWithObject($object);
                return $this->_storeData($entity);
            }
        }
        $object = $this->_mapper->prepareObject($type);
        $type->createWithObject($object);
        return $this->_storeData($type);
    }

    /**
     * Handle put request
     */
    private function _handleUpdate(TypeInterface $type)
    {
        $object = $this->_mapper->getBySubject(trim($this->_data['@subject'], '<>'));
        $entity = $type->createWithObject($object);
        return $this->_storeData($entity);
    }

    private function _storeData(EntityInterface $entity)
    {
        $new_values = $this->_getProperties();
        $object = $entity->getObject();

        foreach ($entity->getChildren() as $fieldname => $node) {
            if (!$node instanceof PropertyInterface) {
                continue;
            }
            /** @var $node PropertyInterface */
            $rdf_name = $node->getAttribute('property');

            $expanded_name = $this->_expandPropertyName($rdf_name, $entity);

            if (array_key_exists($expanded_name, $new_values)) {
                $object = $this->_mapper->setPropertyValue($object, $node, $new_values[$expanded_name]);
            }
        }

        if ($this->_mapper->store($object))
        {
            return $this->_convertToJsonld($object, $entity);
        }

        return null;
    }

    private function _convertToJsonld($object, EntityInterface $controller)
    {
        $jsonld = $this->_data;
        $jsonld['@subject'] = '<' . $this->_mapper->createSubject($object) . '>';
        foreach ($controller->getChildren() as $fieldname => $node) {
            if (!$node instanceof PropertyInterface) {
                continue;
            }
            /** @var $node PropertyInterface */
            $rdf_name = $node->getAttribute('property');

            $expanded_name = '<' . $this->_expandPropertyName($rdf_name, $controller) . '>';

            if (array_key_exists($expanded_name, $jsonld)) {
                $jsonld[$expanded_name] = $this->_mapper->getPropertyValue($object, $node);
            }
        }

        return $jsonld;
    }

    private function _expandPropertyName($name, TypeInterface $controller)
    {
        $name = explode(":", $name);
        $vocabularies = $controller->getVocabularies();
        return $vocabularies[$name[0]] . $name[1];
    }
}
