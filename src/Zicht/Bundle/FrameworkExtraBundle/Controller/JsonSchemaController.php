<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider\DelegatorRefProvider;

class JsonSchemaController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     *
     * @Route("/json-schema-translate/{_locale}/{url}", requirements={"url"=".+"})
     */
    public function translateAction(Request $request, $url)
    {
        return new JsonResponse(
            json_encode($this->get(DelegatorRefProvider::class)->getSchemaData($request->getRequestUri())),
            200,
            [],
            true
        );
    }
}
