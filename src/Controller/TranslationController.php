<?php

namespace Zicht\Bundle\FrameworkExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            ['translator' => '?' . TranslatorInterface::class]
        );
    }

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
     */
    #[Route('/translate/{locale}/{domain}')]
    #[Route('/translate/{locale}')]
    public function translateAction(Request $request, string $locale, ?string $domain = null): JsonResponse
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
     * @param string[] $ids
     * @return array<string, string>
     */
    private function getTranslationsForDomainAndIds(string $locale, ?string $domain, array $ids): array
    {
        $translator = $this->container->get('translator');

        $translations = [];
        foreach ($ids as $id) {
            $translations[$id] = $translator->trans($id, [], $domain, $locale);
        }

        return $translations;
    }

    /** Retrieve all translations for the provided locale and domain */
    private function getTranslationsForDomain(string $locale, ?string $domain): array
    {
        $allMessages = $this->getTranslationsForLocale($locale);

        if (!array_key_exists($domain, $allMessages)) {
            throw new \Exception('Domain ' . $domain . ' not found in the translations for locale ' . $locale);
        }

        return $allMessages[$domain];
    }

    /** Retrieve all translations for the provided locale */
    private function getTranslationsForLocale(string $locale): array
    {
        return $this->container->get('translator')->getMessages($locale);
    }
}
