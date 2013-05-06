<?php

class TFD_Node_Region extends Twig_Node {
    function __construct($region, $body, $line) {
        parent::__construct(array('region' => $region, 'body' => $body), array(), $line);
    }


    function compile(Twig_Compiler $compiler) {
        $compiler
                ->write('ob_start();' . "\n")
                ->subcompile($this->getNode('body'))
                ->write('drupal_set_content(')
                ->subcompile($this->getNode('region'))
                ->raw(', ob_get_clean());' . "\n");
    }
}