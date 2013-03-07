<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Token;
 
class AnnotateTokenParser extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        $info = array();
        $first = $this->parser->getExpressionParser()->parseExpression();

        if ($stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $info['expr']= $first;
        } else {
            $info['name']= $first;
            $info['expr']= $this->parser->getExpressionParser()->parseExpression();
        }

        $node = new AnnotateNode($info);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
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