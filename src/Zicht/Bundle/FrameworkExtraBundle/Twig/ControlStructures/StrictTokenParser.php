<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig_TokenParser;
use Twig_Token;

/**
 * The token parser for a StrictNode node. See StrictNode for more info.
 */
class StrictTokenParser extends Twig_TokenParser
{
    /**
     * @{inheritDoc}
     */
    public function getTag()
    {
        return 'strict';
    }

    /**
     * @{inheritDoc}
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $strictExpr = $this->parser->getExpressionParser()->parseExpression();

        /** @var $stream \Twig_TokenStream */
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideEnd'), true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new StrictNode(['body' => $body, 'expr' => $strictExpr], [], $lineno);
    }


    /**
     * Checks if the token is part of the current control structure.
     *
     * @param Twig_Token $token
     * @return mixed
     */
    public function decideEnd($token)
    {
        return $token->test(array('endstrict'));
    }
}
