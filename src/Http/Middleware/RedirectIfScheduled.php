<?php

namespace TransformStudios\TakeoverRedirects\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TransformStudios\TakeoverRedirects\Redirector;

class RedirectIfScheduled
{
    public function handle(Request $request, Closure $next)
    {
        if ($to = Redirector::getRedirect($request)) {
            return redirect($to);
        }

        return $next($request);
    }
}
