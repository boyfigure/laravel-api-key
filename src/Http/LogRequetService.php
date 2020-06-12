<?php

namespace Offspring\LaravelApiKey\Http;

use Carbon\Carbon;
use Offspring\LaravelApiKey\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LogRequestService
{
    private $header_key;
    private $api_key_id;
    private $source;
    private $url;
    private $status;
    private $request;
    private $response;
    private $key;
    private $ip;

    public function __construct(Request $request)
    {
        $this->header_key = config('apiquard.api_key.name');
        $this->status = 1;
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->url = $request->decodedPath();
        $this->fullUrl = $request->fullUrl();
        $this->request = $request->input();
        unset($this->request['x_user']);
        if (isset($this->request['password'])) {
            $this->request['password'] = '***';
        }
        $header = $request->header($this->header_key);
        $apiKey = ApiKey::getByKey($header);
        if ($apiKey) {
            $this->source = $apiKey->name;
            $this->api_key_id = $apiKey->id;
        }

        $this->key = 'user_log_access';
    }

    public function userLogAccess($response = [], $status = 1)
    {
        try {
            $data = [
                'api_key_id' => $this->api_key_id,
                'source' => $this->source,
                'url' => $this->url,
                'full_url' => $this->fullUrl,
                'status' => $status,
                'ip' => $this->ip,
                'request' => $this->request,
                'response' => $response,
                'date' => Carbon::now()->toDateTimeString()
            ];
            $redis_connect = config('apiquard.cache.redis_db');
            $client = Redis::connection($redis_connect);
            $client->lpush($this->key, json_encode($data));

            return responder()->success([])->toArray();
        } catch (\Exception $e) {
            Log::error($e);
            return responder()->success([])->toArray();
        }
    }


}
