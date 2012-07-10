<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP;
use OpenPSA\CreatePHP\Entity\Property;
use OpenPSA\CreatePHP\Entity\Controller;

/**
 * Setup controllers based on a configuration array
 *
 * @package OpenPSA.CreatePHP
 */
class ArrayLoader
{
    protected $_references = array();

    protected $_config = array();

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    public function getManager(RdfMapper $mapper)
    {
        $manager = new Manager($mapper);
        $controllers = array();
        foreach ($this->_config['controllers'] as $identifier => $config) {
            $controllers[$identifier] = $this->_prepareController($identifier, $mapper, $config);
        }

        foreach ($this->_references as $ref_config) {
            $property_name = $ref_config['property_name'];
            $parent_id = $ref_config['identifier'];
            $ref_id = $ref_config['ref_id'];
            $controllers[$parent_id]->$property_name->setController($controllers[$ref_id]);
        }

        foreach ($controllers as $identifier => $controller) {
            $manager->setController($identifier, $controller);
        }

        if (!empty($this->_config['workflows'])) {
            foreach ($this->_config['workflows'] as $identifier => $classname) {
                $manager->registerWorkflow($identifier, new $classname);
            }
        }

        if (!empty($this->_config['widget'])) {
            $manager->setWidget($this->_prepareWidget($this->_config['widget']));
        }
        return $manager;
    }

    private function _prepareWidget(array $config)
    {
        $widget = new Widget;
        if (!empty($config['urls'])) {
            foreach ($config['urls'] as $type => $url) {
                $widget->registerUrl($type, $url);
            }
        }
        if (!empty($config['options'])) {
            foreach ($config['options'] as $key => $value) {
                $widget->setOption($key, $value);
            }
        }
        if (!empty($config['editors'])) {
            foreach ($config['editors'] as $key => $value) {
                $widget->setEditorConfig($key, $value);
            }
        }

        return $widget;
    }

    private function _prepareController($identifier, $mapper, $config)
    {
        $controller = new Controller($mapper, $config);
        $add_default_vocabulary = false;
        if (!empty($config['properties'])) {
            foreach ($config['properties'] as $property_name => $field_config) {
                if (empty($field_config['nodeType'])) {
                    $classname = 'OpenPSA\CreatePHP\Entity\Property';
                } else {
                    $classname = $field_config['nodeType'];
                }
                $node = new $classname($field_config, $property_name);

                if ($node instanceof Property) {
                    if (empty($field_config['attributes']['property'])) {
                        $field_config['attributes']['property'] = 'createphp:' . $property_name;
                        $add_default_vocabulary = true;
                    }
                }
                if (!empty($field_config['controller'])) {
                    $ref_id = $field_config['controller'];
                    $this->_references[] = array
                    (
                        'identifier' => $identifier,
                        'ref_id' => $ref_id,
                        'property_name' => $property_name,
                    );
                }
                if (!empty($field_config['attributes'])) {
                    $node->setAttributes($field_config['attributes']);
                }
                $node->setParent($controller);
                $controller->$property_name = $node;
            }
        }
        if (!empty($config['attributes'])) {
            $controller->setAttributes($config['attributes']);
        }
        if (!empty($config['vocabularies'])) {
            foreach ($config['vocabularies'] as $prefix => $uri) {
                $controller->setVocabulary($prefix, $uri);
            }
        }
        if ($add_default_vocabulary) {
            $controller->setVocabulary('createphp', 'http://openpsa2.org/createphp/');
        }
        return $controller;
    }
}
