<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        $validUsername = 'admin';
        $validPassword = 'thadeu+luiz';

        if ($username !== $validUsername || $password !== $validPassword) {
            return response('Unauthorized', 401)
                ->header('WWW-Authenticate', 'Basic realm="Clone Catcher Dashboard"');
        }

        return $next($request);
    }
}
