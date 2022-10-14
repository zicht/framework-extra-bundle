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
     * Parses a token and returns a node.
     *
     * @param Token $token A Token instance
     *
     * @return AnnotationsNode A Twig_NodeInterface instance
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideEndAnnotations']);
        $stream->expect('endannotations');
        $stream->expect(Token::BLOCK_END_TYPE);

        return new AnnotationsNode(['body' => $body]);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
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
