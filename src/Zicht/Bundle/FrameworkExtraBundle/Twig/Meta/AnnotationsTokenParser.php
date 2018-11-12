<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Token;

/**
 * Class AnnotationsTokenParser
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Twig\Meta
 */
class AnnotationsTokenParser extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return AnnotationsNode A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideEndAnnotations'));
        $stream->expect('endannotations');
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new AnnotationsNode(array('body' => $body));
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
     * Decide end annotations
     *
     * @param string $token
     * @return mixed
     */
    public function decideEndAnnotations($token)
    {
        return $token->test('endannotations');
    }
}
