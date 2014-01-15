<?php
/**
 * REST service backend
 *
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Type\CollectionDefinitionInterface;
use Midgard\CreatePHP\Type\TypeInterface;

use Midgard\CreatePHP\Entity\EntityInterface;
use Midgard\CreatePHP\Entity\PropertyInterface;

use Midgard\CreatePHP\Helper\NamespaceHelper;

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
    public function __construct(RdfMapperInterface $mapper)
    {
        $this->setMapper($mapper);
    }

    /**
     * Method to encode links to other entities
     *
     * @param string $value the value to encode
     *
     * @return string the value wrapped in <>
     */
    protected function jsonldEncode($value)
    {
        return "<$value>";
    }
    /**
     * @param string $value the value to decode
     *
     * @return string the value without <>
     */
    protected function jsonldDecode($value)
    {
        return trim($value, '<>');
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
     * @param array $data the json-ld data received in the request
     * @param TypeInterface $type the type information for this data
     * @param string $subject the request subject for workflows
     * @param string $method the http request method, one of the HTTP constants,
     *      if omitted, $_SERVER['REQUEST_METHOD'] is used
     *
     * @return null|array if this is a successful post or put, returns the json
     *      data for the processed item
     */
    public function run($data, TypeInterface $type, $subject = null, $method = null)
    {

        if (null === $method) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }

        if (array_key_exists($method, $this->_workflows)) {
            $object = null;
            if (null === $subject && isset($_GET["subject"])) {
                $subject = $_GET["subject"];
            }
            // TODO: workflows should expect subject rather than instance
            $object = $this->_mapper->getBySubject($subject);
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
                return $this->_handleCreate($data, $type);
            case self::HTTP_PUT:
                return $this->_handleUpdate($data, $type, $subject);
            default:
                throw new \UnexpectedValueException("No workflow found to handle $method");
        }
    }

    /**
     * Handle post request
     *
     * Find a reverse mapping to the parent, into received data and type
     * reverse options. The mapping is used to create the entity to store.
     *
     * @param array $received_data
     * @param TypeInterface $type type of the node to create
     * @return array|null
     */
    private function _handleCreate($received_data, TypeInterface $type)
    {
        $parent = null;
        foreach ($type->getRevOptions() as $option) {
            $rdf = NamespaceHelper::expandNamespace($option, $type->getVocabularies());
            $about = $received_data[$this->jsonldEncode($rdf)];

            if (! empty($about)) {
                $parent = $this->_mapper->getBySubject($this->jsonldDecode(current($about)));
                break;
            }
        }

        $object = $this->_mapper->prepareObject($type, $parent);
        $entity = $type->createWithObject($object);

        return $this->_storeData($received_data, $entity);
    }

    /**
     * Handle put request
     */
    private function _handleUpdate($data, TypeInterface $type, $subject = null)
    {
        if (null === $subject) {
            $subject = $this->jsonldDecode($data['@subject']);
        }
        $object = $this->_mapper->getBySubject($subject);
        $entity = $type->createWithObject($object);
        return $this->_storeData($data, $entity);
    }

    private function _storeData($new_values, EntityInterface $entity)
    {
        $object = $entity->getObject();

        foreach ($entity->getChildDefinitions() as $fieldname => $node) {
            if ($node instanceof CollectionInterface) {
                // check for the list of children. The order may have changed
                $rel = $node->getRel();
                $expanded_name = $this->_expandPropertyName($rel, $entity);
                if (array_key_exists($expanded_name, $new_values)) {
                    $expectedOrder = $new_values[$expanded_name];
                    array_walk($expectedOrder, array($this, 'walkChildrenNames'));
                    $this->_mapper->orderChildren($entity, $node, $expectedOrder);
                }
            } elseif ($node instanceof PropertyInterface) {
                /** @var $node PropertyInterface */
                $rdf_name = $node->getProperty();

                $expanded_name = $this->_expandPropertyName($rdf_name, $entity);

                if (array_key_exists($expanded_name, $new_values)) {
                    $object = $this->_mapper->setPropertyValue($object, $node, $new_values[$expanded_name]);
                }
            }
        }

        if ($this->_mapper->store($entity))
        {
            return $this->_convertToJsonld($object, $entity);
        }

        return null;
    }

    public function walkChildrenNames(&$item, $key)
    {
        $item = $this->jsonldDecode($item);
    }

    private function _convertToJsonld($object, $entity)
    {
        $jsonld = array();

        $jsonld['@subject'] = $this->jsonldEncode($this->_mapper->createSubject($object));

        foreach ($entity->getChildDefinitions() as $node) {

            if ($node instanceof PropertyInterface) {
                $rdf_name = $node->getProperty();
                $expanded_name = $this->_expandPropertyName($rdf_name, $entity);

                $jsonld[$expanded_name] = $this->_mapper->getPropertyValue($object, $node);
            }
        }

        return $jsonld;
    }

    /**
     * Expand a property name to use full namespace instead of short name,
     * as used in reference fields. Additionally jsonld-encodes that link.
     *
     * @param string $name the name to expand, including namespace
     * @param TypeInterface $type the type context for the vocabulary
     *
     * @return string the jsonld-encoded expanded name
     *
     * @throws \RuntimeException if the prefix is not in the vocabulary of
     *      $type
     */
    private function _expandPropertyName($name, TypeInterface $type)
    {
        $parts = explode(":", $name);
        $vocabularies = $type->getVocabularies();
        if (!isset($vocabularies[$parts[0]])) {
            throw new \RuntimeException('Undefined namespace prefix \''.$parts[0]."' in '$name'");
        }
        return $this->jsonldEncode($vocabularies[$parts[0]] . $parts[1]);
    }

    /**
     * Register a workflow
     *
     * @param string $identifier
     * @param WorkflowInterface $workflow
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
