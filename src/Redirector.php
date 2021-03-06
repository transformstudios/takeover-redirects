<?php

namespace TransformStudios\TakeoverRedirects;

use Illuminate\Http\Request;

class Redirector
{
    public static function getRedirect(Request $request): ?string
    {
        $redirects = collect(config('takeover-redirects.pages'))
            ->mapInto(ScheduledRedirect::class);

        if ($redirect = $redirects->first(fn (ScheduledRedirect $redirect) => $redirect->active($request))) {
            return $redirect->to();
        }

        return null;
    }
}
