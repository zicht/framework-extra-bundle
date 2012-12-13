<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\DoctrineExtensionsBundle\Doctrine\FunctionNode;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * see https://gist.github.com/919465
 */
/**
 * "RAND" "(" ")"
 */
class Rand extends FunctionNode
{
   public function parse(\Doctrine\ORM\Query\Parser $parser)
   {
       $parser->match(Lexer::T_IDENTIFIER);
       $parser->match(Lexer::T_OPEN_PARENTHESIS);
       $parser->match(Lexer::T_CLOSE_PARENTHESIS);
   }

   public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
   {
       return 'RAND()';
   }
}