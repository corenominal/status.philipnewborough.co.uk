<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Optional API filter for routes where authentication is not required but is
 * still supported. When an API key is present it is validated exactly as
 * ApiFilter does (including admin detection); when absent the request
 * continues unauthenticated.
 */
class OptionalApiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // CORS Policy
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers: apikey, user-uuid, email, Content-Type, Content-Length, Accept-Encoding');
        if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
            exit();
        }

        // No API key provided — allow the request to continue unauthenticated.
        if (! $request->hasHeader('apikey')) {
            return;
        }

        $apikey = $request->header('apikey')->getValue();

        // Empty API key header — treat as unauthenticated rather than erroring.
        if (empty($apikey)) {
            return;
        }

        // Master key check — fast path, no remote call needed.
        $config = config('ApiKeys');
        if ($config->masterKey === $apikey) {
            return;
        }

        // Non-master key: validate against the auth server.
        if (! $request->hasHeader('user-uuid')) {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            exit(json_encode(['error' => 'No user UUID provided.']));
        }

        $userUuid = $request->header('user-uuid')->getValue();
        if (empty($userUuid)) {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            exit(json_encode(['error' => 'Empty user UUID provided.']));
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('Urls')->auth . 'api/keycheck/' . $userUuid . '/' . $apikey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $config->masterKey]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        if (! $response || isset($response->error)) {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            exit(json_encode(['error' => 'Invalid API key.']));
        }

        if (isset($response->is_admin) && $response->is_admin === true) {
            $GLOBALS['is_admin'] = true;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}
