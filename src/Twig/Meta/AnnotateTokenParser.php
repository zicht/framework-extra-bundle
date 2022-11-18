<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class AnnotateTokenParser extends AbstractTokenParser
{
    /**
     * @return AnnotateNode
     */
    public function parse(Token $token): Node
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

    public function getTag(): string
    {
        return 'annotate';
    }
}
