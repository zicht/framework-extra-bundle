<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TranslationController
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Controller
 */
class TranslationController extends ContainerAware
{
    /**
     * Wrapper for the translator-service
     *
     * The id's and domain are optional. When withheld the controller will return all the translations for the locale and (optional) the domain
     * If id's are passed, they should be passed in the query-parameters, ie:
     *
     *  One id:
     *  /translate/nl/messages?id=eticket.can_not_be_rendered_no_barcode
     *
     *  Multiple id's:
     *  /translate/nl/messages?id[]=eticket.can_not_be_rendered_no_barcode&id[]=eticket.col1&id[]=eticket.copy_warning&id[]=form_label.form.email
     *
     * @param Request $request
     * @param string $locale
     * @param string $domain
     * @return JsonResponse
     *
     * @Route("/translate/{locale}/{domain}")
     * @Route("/translate/{locale}")
     */
    public function translateAction(Request $request, $locale, $domain = null)
    {
        $queryIds = $request->query->get('id');

        if ($domain) {
            if ($queryIds) {
                if (!is_array($queryIds)) {
                    $queryIds = [$queryIds];
                }

                $translations = $this->getTranslationsForDomainAndIds($locale, $domain, $queryIds);
            } else {
                $translations = $this->getTranslationsForDomain($locale, $domain);
            }
        } else {
            $translations = $this->getTranslationsForLocale($locale);
        }

        return new JsonResponse(
            $translations
        );
    }

    /**
     * Retrieve all translations for the provided ids (within the provided locale and domain)
     *
     * @param string $locale
     * @param string $domain
     * @param array $ids
     * @return array
     */
    private function getTranslationsForDomainAndIds($locale, $domain, $ids)
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        $translations = [];
        foreach ($ids as $id) {
            $translations[$id] = $translator->trans($id, [], $domain, $locale);
        }

        return $translations;
    }

    /**
     * Retrieve all translations for the provided locale and domain
     *
     * @param string $locale
     * @param string $domain
     * @return array
     * @throws \Exception
     */
    private function getTranslationsForDomain($locale, $domain)
    {
        $allMessages = $this->getTranslationsForLocale($locale);

        if (!array_key_exists($domain, $allMessages)) {
            throw new \Exception('Domain ' . $domain . ' not found in the translations for locale ' . $locale);
        }

        return $allMessages[$domain];
    }

    /**
     * Retrieve all translations for the provided locale
     *
     * @param string $locale
     * @return array
     */
    private function getTranslationsForLocale($locale)
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        return $translator->getMessages($locale);
    }
}
