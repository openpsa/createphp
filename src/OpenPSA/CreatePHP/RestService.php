<?php
/**
 * REST service backend
 *
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP;
use OpenPSA\CreatePHP\Entity\Property;
use OpenPSA\CreatePHP\Entity\Collection;
use OpenPSA\CreatePHP\Entity\Controller;

/**
 * @package OpenPSA.CreatePHP
 */
class RestService
{
    /**
     * The mapper object
     *
     * @var RdfMapper
     */
    protected $_mapper;

    /**
     * The operation verb
     *
     * @var string
     */
    protected $_verb;

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
     * @param RdfMapper $mapper
     */
    public function __construct(RdfMapper $mapper, array $data = null)
    {
        $this->_data = $data;
        $this->_verb = strtolower($_SERVER['REQUEST_METHOD']);
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
     * Workflow setter
     *
     * @param string $identifier
     * @param Workflow $workflow
     */
    public function setWorkflow($identifier, Workflow $workflow)
    {
        $this->_workflows[$identifier] = $workflow;
    }

    /**
     * Mapper setter
     *
     * @param RdfMapper $mapper
     */
    public function setMapper(RdfMapper $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Mapper getter
     *
     * @return RdfMapper
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * Run the service
     */
    public function run(Controller $controller)
    {
        if (array_key_exists($this->_verb, $this->_workflows)) {
            $object = $this->_mapper->getByIdentifier($_REQUEST["subject"]);
            return $this->_workflows[$this->_verb]->run($object);
        }
        switch ($this->_verb) {
            case 'get':
                // do not handle get
                break;
            case 'delete':
                //delete is a workflow, so it's not handled here directly
                break;
            case 'post':
                return $this->_handleCreate($controller);
                break;
            case 'put':
                return $this->_handleUpdate($controller);
                break;
        }
    }

    /**
     * Handle post request
     */
    private function _handleCreate(Controller $controller)
    {
        $received_data = $this->_getProperties();

        $child_controller = null;
        foreach ($controller->getChildren() as $fieldname => $node) {
            if (!$node instanceof Collection) {
                continue;
            }
            $child_controller = $node->getController();
            $parentfield = $this->_expandPropertyName($node->getAttribute('rev'), $child_controller);
            if (!empty($received_data[$parentfield])) {
                $parent_identifier = trim($received_data[$parentfield][0], '<>');
                $parent = $this->_mapper->getByIdentifier($parent_identifier);
                $object = $this->_mapper->prepare_object($child_controller, $parent);
                $child_controller->setObject($object);
                return $this->_storeData($child_controller);
            }
        }
        $object = $this->_mapper->prepareObject($controller);
        $controller->setObject($object);
        return $this->_storeData($controller);
    }

    /**
     * Handle put request
     */
    private function _handleUpdate(Controller $controller)
    {
        $object = $this->_mapper->getByIdentifier(trim($this->_data['@subject'], '<>'));
        $controller->setObject($object);
        return $this->_storeData($controller);
    }

    private function _storeData(Controller $controller)
    {
        $new_values = $this->_getProperties();
        $object = $controller->getObject();

        foreach ($controller->getChildren() as $fieldname => $node) {
            if (!$node instanceof property) {
                continue;
            }
            $rdf_name = $node->get_attribute('property');

            $expanded_name = $this->_expandPropertyName($rdf_name, $controller);

            if (array_key_exists($expanded_name, $new_values)) {
                $object = $this->_mapper->setPropertyValue($object, $node, $new_values[$expanded_name]);
            }
        }

        if ($this->_mapper->store($object))
        {
            return $this->_convertToJsonld($object, $controller);
        }
    }

    private function _convertToJsonld($object, Controller $controller)
    {
        $jsonld = $this->_data;
        $jsonld['@subject'] = '<' . $this->_mapper->createIdentifier($object) . '>';
        foreach ($controller->getChildren() as $fieldname => $node) {
            if (!$node instanceof Property) {
                continue;
            }
            $rdf_name = $node->get_attribute('property');

            $expanded_name = '<' . $this->_expandPropertyName($rdf_name, $controller) . '>';

            if (array_key_exists($expanded_name, $jsonld)) {
                $jsonld[$expanded_name] = $this->_mapper->getPropertyValue($object, $node);
            }
        }

        return $jsonld;
    }

    private function _expandPropertyName($name, Controller $controller)
    {
        $name = explode(":", $name);
        $vocabularies = $controller->getVocabularies();
        return $vocabularies[$name[0]] . $name[1];
    }
}
