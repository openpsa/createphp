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
 * Wrapper for CreateJS's constructor & config
 *
 * @package OpenPSA.CreatePHP
 */
class Widget
{
    private $_urls = array();

    private $_options = array();

    private $_editors = array();

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

        if (!empty($this->_editors))
        {
            $js .= 'editorOptions: {' . "\n";
            foreach ($this->_editors as $name => $config) {
                if (isset($this->_urls['upload'])) {
                    $config = str_replace('__UPLOAD_URL__', $this->_urls['upload'], $config);
                }
                $js .= '"' . $name . '": {' . "\n";
                $js .= $config . "\n";
                $js .= "},\n";
            }
            $js = trim($js, "\n,");
            $js .= '},' . "\n";
        }
        $js = trim($js, "\n,");

        $js .= '});' . "\n";
        $js .= '});' . "\n";

        return $js;
    }

    public function __toString()
    {
        return $this->render();
    }
}
