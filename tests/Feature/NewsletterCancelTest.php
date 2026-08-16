<?php

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('cancel page shows the form for an active subscriber', function () {
    $subscriber = NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'en',
        'is_canceled' => false,
    ]);

    $this->get('/newsletter/cancel/'.$subscriber->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterCancelPage')
            ->where('status', 'form'));
});

test('cancel page shows done status after cancellation', function () {
    $subscriber = NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'en',
        'is_canceled' => false,
    ]);

    $this->get('/newsletter/cancel/'.$subscriber->uuid.'?done=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterCancelPage')
            ->where('status', 'done'));
});

test('unsubscribe cancels the subscription and redirects with done', function () {
    $subscriber = NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'pt_br',
        'is_canceled' => false,
    ]);

    $response = $this->post('/newsletter/unsubscribe/'.$subscriber->uuid);

    $response->assertRedirect(route('newsletter.cancel', [
        'uuid' => $subscriber->uuid,
        'lang' => 'pt_br',
        'done' => 1,
    ]));

    expect($subscriber->refresh()->is_canceled)->toBeTrue()
        ->and($subscriber->date_canceled)->not->toBeNull();
});

test('unsubscribe with an unknown uuid still redirects', function () {
    $response = $this->post('/newsletter/unsubscribe/'.(string) Str::uuid());

    $response->assertRedirect();
});
