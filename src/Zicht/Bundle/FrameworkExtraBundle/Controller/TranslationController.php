<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * @param string $locale
     * @param string $domain
     * @param string $id
     * @return string
     *
     * @Route("/translate/{locale}/{domain}/{id}")
     */
    public function translateAction($locale, $domain, $id) {
        return new JsonResponse(
            [
                'locale' => $locale,
                'domain' => $domain,
                'id' => $id,
                'translation' => $this->container->get('translator')->trans($id, [], $domain, $locale),
            ]
        );
    }
}
