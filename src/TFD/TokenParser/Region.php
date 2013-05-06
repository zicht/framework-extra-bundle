<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class TFD_TokenParser_Region extends Twig_TokenParser {
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $line = $token->getLine();
        $region = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideRegionEnd'), true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new TFD_Node_Region($region, $body, $line);
    }


    public function decideRegionEnd($token) {
        return $token->test('endregion');
    }


    public function getTag() {
        return 'region';
    }

//    public function parse() {
//        $this->region = $this-par
//    }
}