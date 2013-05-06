<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class TFD_Node_Switch_Case extends Twig_Node {
    public function __construct($body, $expression, $fallthrough, $lineno = 0, $tag = null) {
        parent::__construct(
            array(
                'body' => $body
            ),
            array(
                'expression' => $expression,
                'fallthrough' => $fallthrough
            ),
            $lineno,
            $tag
        );
    }
    
}