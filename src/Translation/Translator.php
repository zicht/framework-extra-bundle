<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;

/**
 * Provides zz locale
 *
 * @example To enable this feature the default translator class must be replaced by adding the following to config.yml:
 *          parameters:
 *              translator.class: 'Zicht\Bundle\FrameworkExtraBundle\Translation\Translator'
 *          -- or use the param: --
 *              translator.class: '%zicht_framework_extra.translator.class%'
 * @see \Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\ReplaceTranslatorPass
 */
class Translator extends BaseTranslator
{
    /**
     * {@inheritDoc}
     */
    public function trans($id, array $parameters = [], $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if ($locale == 'zz') {
            if (null === $domain) {
                $domain = 'messages';
            }
            $parts = [
                sprintf('{%s', $id),
                sprintf('@%s', $domain),
            ];
            if (!empty($parameters)) {
                $parts[] = json_encode($parameters);
            }
            $parts[] = '}';
            return join('', $parts);
        } else {
            return parent::trans($id, $parameters, $domain, $locale);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if ($locale == 'zz') {
            if (null === $domain) {
                $domain = 'messages';
            }
            $parts = [
                sprintf('{%s', $id),
                sprintf('#%s', $number),
                sprintf('@%s', $domain),
            ];
            if (!empty($parameters)) {
                $parts[] = sprintf('[%s]', join(', ', array_keys($parameters)));
            }
            $parts[] = '}';
            return join('', $parts);
        } else {
            $parameters['%count%'] = $number;
            return parent::trans($id, $parameters, $domain, $locale);
        }
    }
}
