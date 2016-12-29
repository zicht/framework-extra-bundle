<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
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
     * Id's should be passed in the query-parameters, ie:
     *  /translate/nl/messages?id=eticket.can_not_be_rendered_no_barcode
     *  /translate/nl/messages?id[]=eticket.can_not_be_rendered_no_barcode&id[]=eticket.col1&id[]=eticket.copy_warning&id[]=form_label.form.email
     *
     * @param Request $request
     * @param string $locale
     * @param string $domain
     * @return JsonResponse
     *
     * @Route("/translate/{locale}/{domain}")
     */
    public function translateAction(Request $request, $locale, $domain)
    {
        $response = [
            'locale' => $locale,
            'domain' => $domain,
        ];

        $queryIds = $request->query->get('id');

        if (!is_array($queryIds)) {
            $queryIds = [$queryIds];
        }

        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        $translations = [];
        foreach ($queryIds as $id) {
            $translation = [];
            $translation['id'] = $id;
            $translation['translation'] = $translator->trans($id, [], $domain, $locale);

            $translations[] = $translation;
        }

        return new JsonResponse(
            $response + ['translations' => $translations]
        );
    }
}
