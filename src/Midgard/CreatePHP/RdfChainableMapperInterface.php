<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */
namespace Midgard\CreatePHP;

use Midgard\CreatePHP\Type\TypeInterface;

/**
 * Mappers that implement this interface can be used the the chain mapper.
 *
 * Tests whether the mapper supports and object or can create an object of a type.
 *
 * @package Midgard.CreatePHP
 */
interface RdfChainableMapperInterface extends RdfMapperInterface
{
    /**
     * Get if the object can be handled by this mapper.
     *
     * @param mixed $object
     *
     * @return boolean
     */
    public function supports($object);

    /**
     * Get if this mapper can create this type.
     *
     * @param TypeInterface $type
     *
     * @return boolean
     */
    public function supportsCreate(TypeInterface $type);
}
