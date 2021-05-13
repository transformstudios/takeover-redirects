<?php

namespace TransformStudios\TakeoverRedirects;

use Edalzell\Forma\Forma;
use Statamic\Providers\AddonServiceProvider;
use TransformStudios\TakeoverRedirects\Http\Middleware\RedirectIfScheduled;

class ServiceProvider extends AddonServiceProvider
{
    protected $middlewareGroups = [
        'web' => [
            RedirectIfScheduled::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        Forma::add('transformstudios/takeover-redirects');
    }
}
