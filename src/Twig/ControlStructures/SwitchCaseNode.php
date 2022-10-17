<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Node\Node;

/**
 * Represents a 'case' or a 'default' inside a switch
 */
class SwitchCaseNode extends Node
{
    /**
     * @param Node $body
     * @param array $expression
     * @param int $fallthrough
     * @param int $lineno
     * @param null $tag
     */
    public function __construct($body, $expression, $fallthrough, $lineno = 0, $tag = null)
    {
        parent::__construct(
            [
                'body' => $body,
            ],
            [
                'expression' => $expression,
                'fallthrough' => $fallthrough,
            ],
            $lineno,
            $tag
        );
    }
}
