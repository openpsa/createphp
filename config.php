<?php
/**
 * Config wrapper
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
class config
{
    /**
     * The current schema
     *
     * @var string
     */
    protected $_schema;

    protected $_defaults = array
    (
        'vocabularies' => array(),
        'properties' => array(),
        'attributes' => array(),
        'storage' => false
    );

    protected $_property_defaults = array
    (
        'type' => array('openpsa\createphp\propertyNode')
    );

    protected $_data = array();

    public function __construct(array $data = array())
    {
        foreach ($data as $schema_name => $config)
        {
            if (empty($this->_schema))
            {
                $this->_schema = $schema_name;
            }
            foreach ($config['properties'] as $fieldname => $values)
            {
                $config['properties'][$fieldname] = array_merge($this->_property_defaults, $config['properties'][$fieldname]);
            }
            $this->_data[$schema_name] = array_merge($this->_defaults, $config);
        }
    }

    public function set_schema($schema_name)
    {
        $this->_schema = $schema_name;
    }

    public function get($key)
    {
        return $this->_data[$this->_schema][$key];
    }
}
?>