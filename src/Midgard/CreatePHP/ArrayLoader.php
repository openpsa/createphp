<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Entity\Property;
use Midgard\CreatePHP\Entity\Controller;

use Midgard\CreatePHP\Metadata\RdfDriverArray;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;

/**
 * Setup the manager based on a configuration array
 *
 * @package Midgard.CreatePHP
 */
class ArrayLoader
{
    protected $_references = array();

    protected $_config = array();

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    public function getManager(RdfMapperInterface $mapper)
    {
        $driver = new RdfDriverArray($this->_config['types']);
        $metadata = new RdfTypeFactory($mapper, $driver);

        $manager = new Manager($mapper, $metadata);

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

}
