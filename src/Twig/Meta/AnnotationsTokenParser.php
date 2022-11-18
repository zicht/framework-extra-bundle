<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class AnnotationsTokenParser extends AbstractTokenParser
{
    /**
     * @return AnnotationsNode A Twig_NodeInterface instance
     */
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideEndAnnotations']);
        $stream->expect('endannotations');
        $stream->expect(Token::BLOCK_END_TYPE);

        return new AnnotationsNode(['body' => $body]);
    }

    public function getTag(): string
    {
        return 'annotations';
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function decideEndAnnotations($token)
    {
        return $token->test('endannotations');
    }
}
