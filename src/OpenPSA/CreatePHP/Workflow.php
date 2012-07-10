<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package OpenPSA.CreatePHP
 */

namespace OpenPSA\CreatePHP;

/**
 * Interface for workflow implementations
 *
 * @package OpenPSA.CreatePHP
 */
interface Workflow
{
    /**
     * Get toolbar config for the given object
     *
     * @param mixed $object
     * @return array|null
     */
    public function getToolbarConfig($object);

    /**
     * Execute workflow for the given object
     *
     * @param mixed $object
     * @return array
     */
    public function run($object);
}
