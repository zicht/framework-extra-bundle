<?php

class TFD_TokenParser_Unset extends Twig_TokenParser {
    public function parse(Twig_Token $token) {
        $expression = $this->parser->getExpressionParser()->parseExpression($token);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        return new TFD_Node_Unset(array(), array('expr' => $expression));
    }

    function getTag() {
        return 'unset';
    }
}