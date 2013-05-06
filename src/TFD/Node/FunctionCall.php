<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
/**
 * A functioncall node. Calls the function with the arguments parameter as function
 * arguments. If $isOutput is set, the call is prepended with an 'echo'.
 *
 * @see TFD_TokenParser_FunctionCall
 */
class TFD_Node_FunctionCall extends Twig_Node {
    public $functionName;
    public $isOutput;

    function __construct($functionName, $arguments, $isOutput, $line, $tag) {
        parent::__construct(
            array(),
            array('arguments' => $arguments, 'isOutput' => $isOutput),
            $line,
            $tag
        );
        //TODO inflect functionname to see if it might be a class method callback, and reverse engineer if so
        if(!is_string($functionName) || !is_callable($functionName)) {
            throw new InvalidArgumentException("Currently only supporting regular functions");
        }
        $this->functionName = $functionName;
    }

    
    public function compile(Twig_Compiler $compiler) {
        $compiler->addDebugInfo($this);
        
        if($this->getAttribute('isOutput')) {
            $compiler->write('echo ')->raw($this->functionName);    
        } else {
            $compiler->write($this->functionName);
        }

        $compiler->write("(");
        $i = 0;
        foreach($this->attributes["arguments"] as $param) {
            if($i ++) { // no comma at first argument
                $compiler->raw(', ');
            }
            $compiler->subcompile($param);
        }
        $compiler->raw(");\n");
    }
}