<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Http;

use \Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response {
    const CONTENT_TYPE = 'application/json';

    function __construct($json, $responseCode = 200, $headers = array()) {
        $headers['Content-Type'] = self::CONTENT_TYPE;
        parent::__construct(json_encode($json), $responseCode, $headers);
    }
}