<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Extension\Twig;

use Twig_Token;
use Twig_TokenParser;

use Midgard\CreatePHP\Metadata\RdfTypeFactory;

/**
 * A twig token parser for the createphp tag extension.
 *
 * @package Midgard.CreatePHP
 */
class CreatephpTokenParser extends Twig_TokenParser
{
    private $factory;

    /**
     * Constructor.
     *
     * Attributes can be added to the tag by passing names as the options
     * array. These values, if found, will be passed to the factory and node.
     *
     * @param RdfTypeFactory $factory    The asset factory
     */
    public function __construct(RdfTypeFactory $factory)
    {
        $this->factory = $factory;
    }

    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        // modelvariable
        $modelname = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $var = null;
        if ($stream->test(Twig_Token::NAME_TYPE, 'as')) {
            $stream->next();
            $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
            $var = $stream->expect(Twig_Token::STRING_TYPE)->getValue();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $endtag = 'end'.$this->getTag();
        $test = function(Twig_Token $token) use($endtag) { return $token->test($endtag); };
        $body = $this->parser->subparse($test, true);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new CreatephpNode($body, $modelname, $var, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'createphp';
    }
}
