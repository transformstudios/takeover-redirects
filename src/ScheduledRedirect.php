<?php

namespace TransformStudios\TakeoverRedirects;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class ScheduledRedirect
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function active(Request $request)
    {
        return  $this->current() && ! $this->internal($request) && $this->matches($request);
    }

    public function current(): bool
    {
        return Carbon::now()->isBetween($this->start(), $this->end());
    }

    public function internal(Request $request): bool
    {
        $siteHost = parse_url(Site::current()->absoluteUrl(), PHP_URL_HOST);
        $requestHost = parse_url($request->header('Referer'), PHP_URL_HOST);

        return Str::of($requestHost)->is($siteHost);
    }

    public function matches(Request $request)
    {
        return Str::of($this->from())->is($request->url());
    }

    public function from(): ?string
    {
        return $this->urlFrom('from');
    }

    public function to(): ?string
    {
        return $this->urlFrom('to');
    }

    public function start(): Carbon
    {
        if ($start = Arr::get($this->data, 'start_at')) {
            return Carbon::createFromTimeString($start);
        }

        return Carbon::minValue();
    }

    public function end(): Carbon
    {
        if ($end = Arr::get($this->data, 'end_at')) {
            return Carbon::createFromTimeString($end);
        }

        return Carbon::maxValue();
    }

    private function urlFrom(string $field): ?string
    {
        return EntryAPI::find(Arr::get($this->data, $field))?->absoluteUrl();
    }
}
