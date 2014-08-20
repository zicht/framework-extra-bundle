<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Translation;

use \Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;

/**
 * Class Translator
 * @package Zicht\Bundle\FrameworkExtraBundle\Translation
 */
class Translator extends BaseTranslator
{
    /**
     * @{inheritDoc}
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if ($locale == 'zz') {
            if (empty($parameters)) {
                return sprintf('{%s}', $id);
            } else {
                return sprintf('{%s:%s}', $id, join(', ', array_keys($parameters)));
            }
        } else {
            return parent::trans($id, $parameters, $domain, $locale);
        }
    }

    /**
     * @{inheritDoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if ($locale == 'zz') {
            if (empty($parameters)) {
                return sprintf('{%s:%d}', $id, $number);
            } else {
                return sprintf('{%s:%d:%s}', $id, $number, join(', ', array_keys($parameters)));
            }
        } else {
            return parent::transChoice($id, $number, $parameters, $domain, $locale);
        }
    }
}