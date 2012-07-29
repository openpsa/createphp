<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\NodeInterface;
use Midgard\CreatePHP\Type\CollectionDefinitionInterface;

/**
 * Collection holder. Acts at the same time as a property to a parent entity and
 * and as a holder for entities of other objects which are linked to the first one
 * with some kind of relation
 *
 * @package Midgard.CreatePHP
 */
interface CollectionInterface extends NodeInterface, CollectionDefinitionInterface
{
}
