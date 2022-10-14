<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ParentValidator
{
    /**
     * Validates parents of a MenuItem
     *
     * @param object $object
     */
    public static function validate($object, ExecutionContextInterface $context)
    {
        if (!method_exists($object, 'getParent')) {
            return;
        }

        $tempObject = $object->getParent();

        while ($tempObject !== null) {
            if ($tempObject === $object) {
                $context->buildViolation(
                    'Circular reference error. '
                    . 'An object can not reference with a parent to itself nor to an ancestor of itself'
                )
                    ->atPath('parent')
                    ->addViolation();

                break;
            }

            $tempObject = $tempObject->getParent();
        }
    }
}
