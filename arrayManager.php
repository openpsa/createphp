<?php
/**
 * Setup controllers based on a configuration array
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
class arrayManager extends manager
{
    protected $_references = array();

    public function __construct(rdfMapper $mapper, array $configs)
    {
        parent::__construct($mapper);

        foreach ($configs as $identifier => $config)
        {
            $this->_controllers[$identifier] = $this->_prepare_controller($identifier, $config);
        }

        foreach ($this->_references as $ref_config)
        {
            $property_name = $ref_config['property_name'];
            $parent_id = $ref_config['identifier'];
            $ref_id = $ref_config['ref_id'];
            $this->_controllers[$parent_id]->$property_name->set_controller($this->_controllers[$ref_id]);
        }
    }

    private function _prepare_controller($identifier, $config)
    {
        $controller = new controller($this->_mapper, $config);
        $add_default_vocabulary = false;
        if (!empty($config['properties']))
        {
            foreach ($config['properties'] as $property_name => $property_config)
            {
                if (empty($property_config['type']))
                {
                    $classname = 'openpsa\createphp\propertyNode';
                }
                else
                {
                    $classname = array_shift($property_config['type']);
                }
                $node = new $classname($property_config, $property_name);

                if ($node instanceof propertyNode)
                {
                    if (empty($property_config['rdf_name']))
                    {
                        $rdf_name = 'createphp:' . $property_name;
                        $add_default_vocabulary = true;
                    }
                    else
                    {
                        $rdf_name = $property_config['rdf_name'];
                    }
                    $node->set_attribute('property', $rdf_name);
                }
                if (!empty($property_config['type']))
                {
                    $ref_id = array_shift($property_config['type']);
                    $this->_references[] = array
                    (
                        'identifier' => $identifier,
                        'ref_id' => $ref_id,
                        'property_name' => $property_name,
                    );
                }
                if (!empty($property_config['attributes']))
                {
                    foreach ($property_config['attributes'] as $key => $value)
                    {
                        $node->set_attribute($key, $value);
                    }
                }
                $node->set_parent($controller);
                $controller->$property_name = $node;
            }
        }
        if (!empty($config['attributes']))
        {
            foreach ($config['attributes'] as $key => $value)
            {
                $controller->set_attribute($key, $value);
            }
        }
        if (!empty($config['vocabularies']))
        {
            foreach ($config['vocabularies'] as $prefix => $uri)
            {
                $controller->set_vocabulary($prefix, $uri);
            }
        }
        if ($add_default_vocabulary)
        {
            $controller->set_vocabulary('createphp', 'http://openpsa2.org/createphp/');
        }
        return $controller;
    }
}
?>