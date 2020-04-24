<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig_Token;
use Twig_NodeInterface;

/**
 * The 'with' tag allows a scope-shift into a defined array. The format is as follows:
 *
 * {% with expr [as localName] [, expr2 [as localName2], [....]]  {sandboxed|merged} %}
 *     content
 * {% endwith %}
 */
class WithTokenParser extends \Twig_TokenParser
{
    /** @var string[] */
    private $options = [
        'merged',
        'sandboxed',
        'always',
    ];

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string
     */
    public function getTag()
    {
        return 'with';
    }

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $options = [];
        $stream = $this->parser->getStream();
        $start = $stream->getCurrent();
        $arguments = [];
        do {
            $value = $this->parser->getExpressionParser()->parseExpression();
            if ($stream->test('as')) {
                $stream->expect('as');
                $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
            } else {
                $name = null;
            }
            $arguments[] = ['name' => $name, 'value' => $value];

            $end = !$stream->test(Twig_Token::PUNCTUATION_TYPE, ',');
            if (!$end) {
                $stream->expect(Twig_Token::PUNCTUATION_TYPE, ',');
            }
        } while (!$end);

        while ($stream->test($this->options)) {
            $options[] = $stream->expect($this->options)->getValue();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWithEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new WithNode($arguments, $body, $options, $start->getLine(), $start->getValue());
    }


    /**
     * Checks for the end of the control structure.
     *
     * @param Twig_Token $token
     * @return bool
     */
    public function decideWithEnd($token)
    {
        return $token->test('endwith');
    }
}
