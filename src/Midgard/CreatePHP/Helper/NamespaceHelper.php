<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author Adrien Nicolet <adrien.nicolet@gmail.com>
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Helper;

/**
 * Class with static methods to help with namespaced properties
 */
class NamespaceHelper
{
    /**
     * Expand a property name to use full namespace instead of short name,
     * as used in reference fields.
     *
     * @param string $name the name to expand, including namespace (e.g. sioc:Post)
     * @param array $vocabularies vocabulary to use for the expanding
     *
     * @return string the expanded name
     *
     * @throws \RuntimeException if the prefix is not in the vocabulary of
     *      $type
     */
    public static function expandNamespace($name, $vocabularies)
    {
        if (false === strpos($name, ':')) {
            if (isset($vocabularies[''])) {
                return $vocabularies[''] . $name;
            }
            return $name;
        }
        list($prefix, $localname) = explode(":", $name);
        if (!isset($vocabularies[$prefix])) {
            throw new \RuntimeException("Undefined namespace prefix '$prefix' in '$name'");
        }

        return $vocabularies[$prefix] . $localname;
    }
}