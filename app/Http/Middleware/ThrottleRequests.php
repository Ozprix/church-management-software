<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottle;

class ThrottleRequests extends BaseThrottle
{
    /**
     * Resolve the number of attempts if the user is authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function resolveMaxAttempts($request, $maxAttempts)
    {
        if (in_array('api', $request->route()->middleware())) {
            return 60; // 60 attempts per minute for API
        }

        return $maxAttempts;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->ip()
        );
    }
}
