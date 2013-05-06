<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
/**
 * Tokenparser to parse an arbitrary functioncall, in the format:
 * {% functionName arg1, arg2, arg3 ... %}
 *
 * The parser generates a TFD_Node_FunctionCall AST Node
 */
class TFD_TokenParser_FunctionCall extends Twig_TokenParser {
    public $tagName;
    public $functionName;
    public $defaultArguments;

    /**
     * @param  string $tagName        The twig tag name to use
     * @param  string $functionName    The function to call. If empty, tagName is assumed to be the function name
     * @param  bool   $isOutput        Whether or not to output the function's result
     */
    function __construct($tagName, $functionName = null, $isOutput = true) {
        if(is_null($functionName)) {
            $functionName = $tagName;
        }
        $this->functionName = $functionName;
        $this->tagName = $tagName;
        $this->isOutput = $isOutput;
    }

    /**
     * Parses a function call. Arguments are comma separated, but not enclosed in parentheses,
     * so, e.g.:
     *
     * {% function arg1, arg2 %}
     *
     * Arguments are not mandatory, and there is no check on required arguments until runtime
     * by PHP
     * 
     * @param Twig_Token $token
     * @return TFD_Node_FunctionCall
     */
    public function parse(Twig_Token $token) {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $arguments = array();
        while(!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $arguments[]= $this->parser->getExpressionParser()->parseExpression();

            if(!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
                $stream->expect(Twig_Token::OPERATOR_TYPE, ',');
            }
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new TFD_Node_FunctionCall($this->functionName, $arguments, $this->isOutput, $lineno, $this->getTag());
    }


    /**
     * Returns the tagname configured at construction
     * 
     * @return string
     */
    function getTag() {
        return $this->tagName;
    }
}