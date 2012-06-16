<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package openpsa.createphp
 */

namespace openpsa\createphp;

/**
 * Interface for workflow implementations
 *
 * @package openpsa.createphp
 */
interface workflow
{
    /**
     * Get toolbar config for the given object
     *
     * @param mixed $object
     * @return array|null
     */
    public function get_toolbar_config($object);
}
?>