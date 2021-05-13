<?php

namespace TransformStudios\TakeoverRedirects\Tests\Unit;

use Illuminate\Support\Carbon;
use Spatie\TestTime\TestTime;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use TransformStudios\TakeoverRedirects\Http\Middleware\RedirectIfScheduled;
use TransformStudios\TakeoverRedirects\Tests\TestCase;

class RedirectTest extends TestCase
{
    private Entry $homeEntry;
    private Entry $fooEntry;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $collection = Collection::make('pages')
            ->layout('welcome')
            ->routes('/{slug}')
            ->template('welcome')
            ->save();
        $this->homeEntry = (new Entry)
            ->id('home-id')
            ->slug('home')
            ->collection($collection);
        $this->fooEntry = (new Entry)
            ->id('foo-id')
            ->slug('foo')
            ->collection($collection);

        $this->homeEntry->save();
        $this->fooEntry->save();
    }

    /** @test */
    public function no_redirect_if_not_configured()
    {
        $this
            ->get('/foo', ['Referer' => 'http://redirect.test/foo'])
            ->assertOK();
    }

    /** @test */
    public function middleware_attached_to_web_routes()
    {
        $this->get('/home');

        $middleware = app('router')->gatherRouteMiddleware(request()->route());

        $this->assertContains(RedirectIfScheduled::class, $middleware);
    }

    /** @test */
    public function will_redirect_with_no_referer()
    {
        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        $this
            ->get('/home')
            ->assertRedirect('/foo');
    }

    /** @test */
    public function will_redirect_with_external_referer()
    {
        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        $this
                ->get('/home', ['Referer' => 'https://transformstudios.com'])
                ->assertRedirect('/foo');
    }

    /** @test */
    public function will_not_redirect_with_relative_site_url()
    {
        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        $this
            ->get('/home', ['Referer' => 'http://redirect.test'])
            ->assertOk();
    }

    /** @test */
    public function will_not_redirect_if_before_start_time()
    {
        TestTime::freezeAtSecond();

        $redirect = [
            'from' => 'foo-id',
            'to' => 'home-id',
            'start_at' => Carbon::now()->toDateTimeString(),
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        TestTime::subSecond();

        $this
            ->get('/foo')
            ->assertOk();
    }

    /** @test */
    public function will_redirect_if_after_or_at_start_time()
    {
        TestTime::freezeAtSecond();

        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
            'start_at' => Carbon::now()->toDateTimeString(),
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        $this
            ->get('/home')
            ->assertRedirect('/foo');

        TestTime::addSecond();

        $this
            ->get('/home')
            ->assertRedirect('/foo');
    }

    /** @test */
    public function will_not_redirect_if_after_end_time()
    {
        TestTime::freezeAtSecond();

        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
            'end_at' => Carbon::now()->toDateTimeString(),
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        TestTime::addSecond();

        $this
            ->get('/home')
            ->assertOk();
    }

    /** @test */
    public function will_redirect_if_before_or_at_end_time()
    {
        TestTime::freezeAtSecond();

        $redirect = [
            'from' => 'home-id',
            'to' => 'foo-id',
            'end_at' => Carbon::now()->toDateTimeString(),
        ];

        config(['takeover-redirects.pages' => [$redirect]]);

        $this
            ->get('/home')
            ->assertRedirect('/foo');

        TestTime::subSecond();

        $this
            ->get('/home')
            ->assertRedirect('/foo');
    }
}
