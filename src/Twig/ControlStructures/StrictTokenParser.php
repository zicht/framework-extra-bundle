<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * The token parser for a StrictNode node. See StrictNode for more info.
 */
class StrictTokenParser extends AbstractTokenParser
{
    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'strict';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(Token $token)
    {
        $lineno = $token->getLine();

        $strictExpr = $this->parser->getExpressionParser()->parseExpression();

        $stream = $this->parser->getStream();
        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'decideEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

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
        return $token->test(['endstrict']);
    }
}
