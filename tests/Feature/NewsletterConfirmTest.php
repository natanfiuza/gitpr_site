<?php

use App\Models\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('confirmation page shows the form for a valid link', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    $this->get('/newsletter/confirm/'.$confirmation->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterConfirmPage')
            ->where('status', 'form')
            ->where('email', 'user@example.com'));
});

test('confirmation page shows not_found for an unknown uuid', function () {
    $this->get('/newsletter/confirm/'.(string) Str::uuid())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterConfirmPage')
            ->where('status', 'not_found'));
});

test('confirmation page shows expired after 24 hours', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    NewsletterConfirmation::where('id', $confirmation->id)
        ->update(['created_at' => now()->subHours(25)]);

    $this->get('/newsletter/confirm/'.$confirmation->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterConfirmPage')
            ->where('status', 'expired'));
});

test('confirmation page shows already_confirmed for a confirmed link', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
        'is_confirmed' => true,
        'date_confirmed' => now(),
    ]);

    $this->get('/newsletter/confirm/'.$confirmation->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NewsletterConfirmPage')
            ->where('status', 'already_confirmed'));
});

test('confirming persists the subscriber and marks the confirmation', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    $response = $this->post('/newsletter/confirm/'.$confirmation->uuid, [
        'name' => 'User',
        'email' => 'user@example.com',
        'github' => 'octocat',
        'phone' => '+55 11 99999-9999',
        'lang' => 'pt_br',
    ]);

    $response->assertRedirect('/newsletter/confirm/'.$confirmation->uuid.'?lang=pt_br');

    $subscriber = NewsletterSubscriber::where('email', 'user@example.com')->first();
    expect($subscriber->name)->toBe('User')
        ->and($subscriber->github)->toBe('octocat')
        ->and($subscriber->phone)->toBe('+55 11 99999-9999')
        ->and($subscriber->lang)->toBe('pt_br')
        ->and($subscriber->is_canceled)->toBeFalse()
        ->and($subscriber->date_canceled)->toBeNull()
        ->and($subscriber->uuid)->not->toBeNull();

    expect($confirmation->refresh()->is_confirmed)->toBeTrue()
        ->and($confirmation->date_confirmed)->not->toBeNull();
});

test('confirming with a different email fails', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    $this->post('/newsletter/confirm/'.$confirmation->uuid, [
        'name' => 'User',
        'email' => 'other@example.com',
        'lang' => 'en',
    ])->assertStatus(422);
});

test('confirming through an expired link does not confirm', function () {
    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    NewsletterConfirmation::where('id', $confirmation->id)
        ->update(['created_at' => now()->subHours(25)]);

    $response = $this->post('/newsletter/confirm/'.$confirmation->uuid, [
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'en',
    ]);

    $response->assertRedirect('/newsletter/confirm/'.$confirmation->uuid);
    expect($confirmation->refresh()->is_confirmed)->toBeFalse();
    expect(NewsletterSubscriber::count())->toBe(0);
});

test('confirming again reactivates a canceled subscriber', function () {
    $subscriber = NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Old',
        'email' => 'user@example.com',
        'lang' => 'en',
        'is_canceled' => true,
        'date_canceled' => now(),
    ]);

    $confirmation = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    $this->post('/newsletter/confirm/'.$confirmation->uuid, [
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'en',
    ]);

    expect($subscriber->refresh()->is_canceled)->toBeFalse()
        ->and($subscriber->date_canceled)->toBeNull()
        ->and($subscriber->name)->toBe('User');
});

test('only one confirmed record per email', function () {
    NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
        'is_confirmed' => true,
        'date_confirmed' => now(),
    ]);

    $second = NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
    ]);

    $this->post('/newsletter/confirm/'.$second->uuid, [
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'en',
    ]);

    expect(NewsletterConfirmation::where('email', 'user@example.com')
        ->where('is_confirmed', true)->count())->toBe(1);

    // The second link was already superseded by the first confirmation.
    expect($second->refresh()->is_confirmed)->toBeFalse();
});
