<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class TFD_TokenParser_Switch extends Twig_TokenParser {
    /**
     * Gets the tag name associated with this token parser.
     *
     * @param string The tag name
     */
    public function getTag() {
        return 'switch';
    }

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token) {
        $lineno = $token->getLine();

        $switchExpr = $this->parser->getExpressionParser()->parseExpression();

        /** @var $stream Twig_TokenStream */
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);


        // skip whitespace between switch and first case
        while($stream->test(Twig_Token::TEXT_TYPE)) {
            if(trim($stream->getCurrent()->getValue()) != '') {
                $content = $stream->getCurrent()->getValue();
                throw new Twig_SyntaxError("Can not render content '$content' directly after switch", $stream->getCurrent()->getLine());
            }
            $stream->next();
        }
        $stream->expect(Twig_Token::BLOCK_START_TYPE);

        $tests = array();
        
        while(!$stream->test('endswitch')) {
            $token = $stream->expect(Twig_Token::NAME_TYPE, array('case', 'default'));
            switch($token->getValue()) {
                case 'case':
                    $caseExpr = array();
                    $caseExpr[]= $this->parser->getExpressionParser()->parseExpression();
                    while($stream->test(Twig_Token::OPERATOR_TYPE, ',')) {
                        $stream->next();
                        $caseExpr[]= $this->parser->getExpressionParser()->parseExpression();
                    }
                    break;
                case 'default':
                    $caseExpr = null;
                    break;
            }

            $fallthrough = false;
            if($stream->test('fallthrough')) {
                $stream->next();
                $fallthrough = true;
            }
            $stream->expect(Twig_Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse(array($this, 'decideSwitchFork'));

            $tests[]= new TFD_Node_Switch_Case(
                $body,
                $caseExpr,
                $fallthrough,
                $token->getLine(),
                $token->getValue()
            );
        }

        $stream->expect('endswitch');
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new TFD_Node_Switch(new Twig_Node($tests), $switchExpr, $lineno);
    }


    public function decideSwitchFork($token) {
        return $token->test(array('case', 'default', 'endswitch'));
    }
}

