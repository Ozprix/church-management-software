<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAttempts
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login.' . $request->ip();
        $maxAttempts = 5; // Max attempts before requiring CAPTCHA

        // Check if the user has exceeded the maximum number of login attempts
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // Add CAPTCHA requirement to the request
            $request->merge(['requires_captcha' => true]);
            
            // If this is a form submission, validate the CAPTCHA
            if ($request->isMethod('post')) {
                $request->validate([
                    'g-recaptcha-response' => 'required|captcha',
                ], [
                    'g-recaptcha-response.required' => 'Please complete the CAPTCHA verification.',
                    'g-recaptcha-response.captcha' => 'CAPTCHA verification failed. Please try again.',
                ]);
            }
        } else {
            $request->merge(['requires_captcha' => false]);
        }

        return $next($request);
    }
}
