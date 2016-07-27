<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Http;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

/**
 * Class JsonResponse
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Http
 *
 * @deprecated use Symfony's one instead
 */
class JsonResponse extends BaseJsonResponse
{
    /**
     * @deprecated
     */
    const CONTENT_TYPE = 'application/json';
}
