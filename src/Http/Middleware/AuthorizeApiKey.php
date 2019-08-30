<?php

namespace Offspring\LaravelApiKey\Http\Middleware;

use Closure;
use Offspring\LaravelApiKey\Models\ApiKey;
use Offspring\LaravelApiKey\Models\ApiKeyAccessEvent;
use Illuminate\Http\Request;

class AuthorizeApiKey
{
    protected $header_key;
    protected $enable_log_access_event;

    public function __construct()
    {
        $this->header_key = config('apiquard.api_key.name');
        $this->enable_log_access_event = config('apiquard.enable_log_access_event');
    }

    /**
     * Handle the incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header($this->header_key);
        $apiKey = ApiKey::getByKey($header);

        if (isset($apiKey)) {
            if ($this->enable_log_access_event) {
                $this->logAccessEvent($request, $apiKey);
            }
            return $next($request);
        }
        return response()->json(['success' => false, 'data' => [
            'message' => 'Unauthorized'
        ]], 401, array(), JSON_PRETTY_PRINT);
    }

    /**
     * Log an API key access event
     *
     * @param Request $request
     * @param ApiKey $apiKey
     */
    protected function logAccessEvent(Request $request, ApiKey $apiKey)
    {
        $event = new ApiKeyAccessEvent;
        $event->api_key_id = $apiKey->id;
        $event->ip_address = $request->ip();
        $event->url = $request->fullUrl();
        $event->save();
    }
}
