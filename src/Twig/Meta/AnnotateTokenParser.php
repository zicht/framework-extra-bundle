<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class AnnotateTokenParser extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Token $token A Token instance
     *
     * @return AnnotateNode A Twig_NodeInterface instance
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        $info = [];
        $first = $this->parser->getExpressionParser()->parseExpression();

        if ($stream->test(Token::BLOCK_END_TYPE)) {
            $info['expr'] = $first;
        } else {
            $info['name'] = $first;
            $info['expr'] = $this->parser->getExpressionParser()->parseExpression();
            if (!$stream->test(Token::BLOCK_END_TYPE)) {
                $info['prio'] = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $node = new AnnotateNode($info);
        $stream->expect(Token::BLOCK_END_TYPE);

        return $node;
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'annotate';
    }
}
