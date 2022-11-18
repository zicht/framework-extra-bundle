<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * The 'with' tag allows a scope-shift into a defined array. The format is as follows:
 *
 * {% with expr [as localName] [, expr2 [as localName2], [....]]  {sandboxed|merged} %}
 *     content
 * {% endwith %}
 */
class WithTokenParser extends AbstractTokenParser
{
    /** @var string[] */
    private $options = [
        'merged',
        'sandboxed',
        'always',
    ];

    public function getTag(): string
    {
        return 'with';
    }

    /**
     * @return WithNode
     */
    public function parse(Token $token): Node
    {
        $options = [];
        $stream = $this->parser->getStream();
        $start = $stream->getCurrent();
        $arguments = [];
        do {
            $value = $this->parser->getExpressionParser()->parseExpression();
            if ($stream->test('as')) {
                $stream->expect('as');
                $name = $stream->expect(Token::NAME_TYPE)->getValue();
            } else {
                $name = null;
            }
            $arguments[] = ['name' => $name, 'value' => $value];

            $end = !$stream->test(Token::PUNCTUATION_TYPE, ',');
            if (!$end) {
                $stream->expect(Token::PUNCTUATION_TYPE, ',');
            }
        } while (!$end);

        while ($stream->test($this->options)) {
            $options[] = $stream->expect($this->options)->getValue();
        }

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWithEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);
        return new WithNode($arguments, $body, $options, $start->getLine(), $start->getValue());
    }

    /**
     * Checks for the end of the control structure.
     *
     * @param Token $token
     * @return bool
     */
    public function decideWithEnd($token)
    {
        return $token->test('endwith');
    }
}
