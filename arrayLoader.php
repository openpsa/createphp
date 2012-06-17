<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;
use openpsa\createphp\entity\property;
use openpsa\createphp\entity\controller;

/**
 * Setup controllers based on a configuration array
 *
 * @package openpsa.createphp
 */
class arrayLoader
{
    protected $_references = array();

    protected $_config = array();

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    public function get_manager(rdfMapper $mapper)
    {
        $manager = new manager($mapper);
        $controllers = array();
        foreach ($this->_config['controllers'] as $identifier => $config)
        {
            $controllers[$identifier] = $this->_prepare_controller($identifier, $mapper, $config);
        }

        foreach ($this->_references as $ref_config)
        {
            $property_name = $ref_config['property_name'];
            $parent_id = $ref_config['identifier'];
            $ref_id = $ref_config['ref_id'];
            $controllers[$parent_id]->$property_name->set_controller($controllers[$ref_id]);
        }

        foreach ($controllers as $identifier => $controller)
        {
            $manager->set_controller($identifier, $controller);
        }

        if (!empty($this->_config['workflows']))
        {
            foreach ($this->_config['workflows'] as $identifier => $classname)
            {
                $manager->register_workflow($identifier, new $classname);
            }
        }
        return $manager;
    }

    private function _prepare_controller($identifier, $mapper, $config)
    {
        $controller = new controller($mapper, $config);
        $add_default_vocabulary = false;
        if (!empty($config['properties']))
        {
            foreach ($config['properties'] as $property_name => $field_config)
            {
                if (empty($field_config['nodeType']))
                {
                    $classname = 'openpsa\createphp\entity\property';
                }
                else
                {
                    $classname = $field_config['nodeType'];
                }
                $node = new $classname($field_config, $property_name);

                if ($node instanceof property)
                {
                    if (empty($field_config['attributes']['property']))
                    {
                        $field_config['attributes']['property'] = 'createphp:' . $property_name;
                        $add_default_vocabulary = true;
                    }
                }
                if (!empty($field_config['controller']))
                {
                    $ref_id = $field_config['controller'];
                    $this->_references[] = array
                    (
                        'identifier' => $identifier,
                        'ref_id' => $ref_id,
                        'property_name' => $property_name,
                    );
                }
                if (!empty($field_config['attributes']))
                {
                    $node->set_attributes($field_config['attributes']);
                }
                $node->set_parent($controller);
                $controller->$property_name = $node;
            }
        }
        if (!empty($config['attributes']))
        {
            $node->set_attributes($config['attributes']);
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