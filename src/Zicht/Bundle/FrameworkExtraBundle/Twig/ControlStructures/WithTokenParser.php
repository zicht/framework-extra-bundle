<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use \Twig_Token;
use \Twig_TokenParser;
use \Twig_NodeInterface;

/**
 * The 'with' tag allows a scope-shift into a defined array. The format is as follows:
 *
 * {% with expr [as localName] [, expr2 [as localName2], [....]]  {sandboxed|merged} %}
 *     content
 * {% endwith %}
 *
 * The with construct sets the argument as the current context. If an 'as name' is defined,
 * the variable is defined as that local name. The two flags sandboxed or merged define an
 * additional behaviour, allowing to sandbox the contents of the construct from the current
 * scope (which results in having only the defined variables in the current scope), or merging
 * them with the current scope respectively. If none of the flags is defined, the current
 * context is stacked in the context as _parent. After the end of the with construct, the
 * context is restored.
 *
 * Example:
 * Assume the following context:
 * array(
 *    'foo' => array(
 *       'name' => 'Foo',
 *       'id'   => 1
 *    ),
 *    'bar' => array(
 *       'name' => 'Bar',
 *       'id'   => 2
 *    )
 * )
 *
 * {% with foo %}
 *    {{ id }}: {{ name }} {# would output: "1: Foo" #}
 * {% endwith %}
 *
 * {% with foo as baz %}
 *    {{ baz.id }}: {{ baz.name }} {# would output: "1: Foo" #}
 * {% endwith %}
 *
 * {% with foo as bar, bar as foo %}
 *    {{ bar.id }}: {{ bar.name }} {# would output: "1: Foo" #}
 *    {{ foo.id }}: {{ foo.name }} {# would output: "2: Bar" #}
 * {% endwith %}
 *
 * {% with foo merged %}
 *    {{ foo.id }}: {{ foo.name }} {# would output: "1: Foo" #}
 *    {{ id }}: {{ name }} {# would output: "1: Foo" #}
 * {% endwith %}
 *
 * etcetera.
 */
class WithTokenParser extends \Twig_TokenParser
{
    private $options = array(
        'merged',
        'sandboxed',
        'always'
    );

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
        $options = array();
        $stream = $this->parser->getStream();
        $start = $stream->getCurrent();
        $arguments = array();
        do {
            $value = $this->parser->getExpressionParser()->parseExpression();
            if ($stream->test('as')) {
                $stream->expect('as');
                $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
            } else {
                $name = null;
            }
            $arguments[] = array('name' => $name, 'value' => $value);

            $end = !$stream->test(Twig_Token::PUNCTUATION_TYPE, ',');
            if (!$end) {
                $stream->expect(Twig_Token::PUNCTUATION_TYPE, ',');
            }
        } while (!$end);

        while ($stream->test($this->options)) {
            $options[] = $stream->expect($this->options)->getValue();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideWithEnd'), true);
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