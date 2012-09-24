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
use Midgard\CreatePHP\Manager;

/**
 * Wrapper for CreateJS's constructor & config
 *
 * @package Midgard.CreatePHP
 */
class Widget
{
    private $_urls = array();

    private $_options = array();

    private $_editors = array();

    /**
     * The manager instance
     *
     * @var Midgard\CreatePHP\Manager
     */
    private $_manager;

    public function __construct(Manager $manager)
    {
        $this->_manager = $manager;
    }

    public function registerUrl($type, $url)
    {
        $this->_urls[$type] = $url;
    }

    public function setEditorConfig($key, $value)
    {
        $this->_editors[$key] = $value;
    }

    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
    }

    public function render()
    {
        $js = '$(document).ready(function() {' . "\n";
        $js .= '$("body").midgardCreate({' . "\n";

        if (isset($this->_urls['rest'])) {
            $js .= 'url: function() {' . "\n";
            $js .= 'return "' . $this->_urls['rest'] . '?subject=" + this.id' . "\n";
            $js .= '},' . "\n";
        }

        foreach ($this->_options as $name => $value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
            } elseif ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            }
            $js .= $name . ': ' . $value . ",\n";
        }

        if (isset($this->_urls['workflows']))
        {
            $js .= 'workflows: {' . "\n";
            $js .= 'url: function(model) {' . "\n";
            $js .= 'return "' . $this->_urls['workflows'] . '?subject=" + model.id' . "\n";
            $js .= '}' . "\n";
            $js .= '},' . "\n";
        }

        $js = trim($js, "\n,");

        $js .= '});' . "\n";

        if (!empty($this->_editors))
        {
            foreach ($this->_editors as $name => $config) {
                if (isset($this->_urls['upload'])) {
                    $config = str_replace('__UPLOAD_URL__', $this->_urls['upload'], $config);
                }
                $js .= "\n\$('body').midgardCreate('configureEditor', '" . $name . "', 'halloWidget', {" . $config . "});\n";
            }
        }

        foreach ($this->_manager->getLoadedTypes() as $identifier => $type)
        {
            foreach ($type->getChildDefinitions() as $key => $child)
            {
                if ($child instanceof Property)
                {
                    $js .= "\n\$('body').midgardCreate('setEditorForProperty', '" . $child->getProperty() . "', '" . $child->getEditor() . "');\n";
                }
            }
        }

        $js .= '});' . "\n";

        return $js;
    }

    public function __toString()
    {
        return $this->render();
    }
}
