<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig_Error_Syntax;
use Twig_TokenParser;
use Twig_Token;
use Twig_Node;

/**
 * Class SwitchTokenParser
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures
 */
class SwitchTokenParser extends Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'switch';
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $switchExpr = $this->parser->getExpressionParser()->parseExpression();

        /** @var $stream Twig_TokenStream */
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);


        // skip whitespace between switch and first case
        while ($stream->test(Twig_Token::TEXT_TYPE)) {
            if (trim($stream->getCurrent()->getValue()) != '') {
                $content = $stream->getCurrent()->getValue();
                throw new Twig_Error_Syntax("Can not render content '$content' directly after switch", $stream->getCurrent()->getLine());
            }
            $stream->next();
        }
        $stream->expect(Twig_Token::BLOCK_START_TYPE);

        $tests = array();

        while (!$stream->test('endswitch')) {
            $token = $stream->expect(Twig_Token::NAME_TYPE, array('case', 'default'));
            switch ($token->getValue()) {
                case 'case':
                    $caseExpr   = array();
                    $caseExpr[] = $this->parser->getExpressionParser()->parseExpression();
                    while ($stream->test(Twig_Token::OPERATOR_TYPE, ',')) {
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
            $stream->expect(Twig_Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse(array($this, 'decideSwitchFork'));

            $tests[] = new SwitchCaseNode(
                $body,
                $caseExpr,
                $fallthrough,
                $token->getLine(),
                $token->getValue()
            );
        }

        $stream->expect('endswitch');
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new SwitchNode(new \Twig_Node($tests), $switchExpr, $lineno);
    }


    /**
     * Checks if the token is part of the current control structure.
     *
     * @param Twig_Token $token
     * @return mixed
     */
    public function decideSwitchFork($token)
    {
        return $token->test(array('case', 'default', 'endswitch'));
    }
}
