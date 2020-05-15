<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class SwitchTokenParser extends AbstractTokenParser
{
    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'switch';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(Token $token)
    {
        $lineno = $token->getLine();

        $switchExpr = $this->parser->getExpressionParser()->parseExpression();

        $stream = $this->parser->getStream();
        $stream->expect(Token::BLOCK_END_TYPE);


        // skip whitespace between switch and first case
        while ($stream->test(Token::TEXT_TYPE)) {
            if (trim($stream->getCurrent()->getValue()) != '') {
                $content = $stream->getCurrent()->getValue();
                throw new SyntaxError("Can not render content '$content' directly after switch", $stream->getCurrent()->getLine());
            }
            $stream->next();
        }
        $stream->expect(Token::BLOCK_START_TYPE);

        $tests = [];

        while (!$stream->test('endswitch')) {
            $token = $stream->expect(Token::NAME_TYPE, ['case', 'default']);
            switch ($token->getValue()) {
                case 'case':
                    $caseExpr = [];
                    $caseExpr[] = $this->parser->getExpressionParser()->parseExpression();
                    while ($stream->test(Token::OPERATOR_TYPE, ',')) {
                        $stream->next();
                        $caseExpr[] = $this->parser->getExpressionParser()->parseExpression();
                    }
                    break;
                case 'default':
                    $caseExpr = null;
                    break;
            }

            $fallthrough = false;
            if ($stream->test('fallthrough')) {
                $stream->next();
                $fallthrough = true;
            }
            $stream->expect(Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse([$this, 'decideSwitchFork']);

            $tests[] = new SwitchCaseNode(
                $body,
                $caseExpr,
                $fallthrough,
                $token->getLine(),
                $token->getValue()
            );
        }

        $stream->expect('endswitch');
        $stream->expect(Token::BLOCK_END_TYPE);

        return new SwitchNode(new Node($tests), $switchExpr, $lineno);
    }


    /**
     * Checks if the token is part of the current control structure.
     *
     * @param Token $token
     * @return mixed
     */
    public function decideSwitchFork($token)
    {
        return $token->test(['case', 'default', 'endswitch']);
    }
}
