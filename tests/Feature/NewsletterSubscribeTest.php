<?php

use App\Mail\CancelLinkMail;
use App\Mail\ConfirmationMail;
use App\Models\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('subscribe creates a pending confirmation and sends the confirmation email', function () {
    Mail::fake();

    $response = $this->from('/index')->post('/newsletter/subscribe', [
        'email' => 'user@example.com',
        'lang' => 'pt_br',
    ]);

    $response->assertRedirect('/index');
    $response->assertSessionHas('newsletter', ['status' => 'sent']);

    $confirmation = NewsletterConfirmation::where('email', 'user@example.com')->first();
    expect($confirmation)->not->toBeNull()
        ->and($confirmation->is_confirmed)->toBeFalse();

    Mail::assertSent(ConfirmationMail::class, function (ConfirmationMail $mail) use ($confirmation) {
        return $mail->lang === 'pt_br'
            && str_contains($mail->url, '/newsletter/confirm/'.$confirmation->uuid);
    });
});

test('resubmitting before confirmation reuses the same uuid', function () {
    Mail::fake();

    $this->post('/newsletter/subscribe', ['email' => 'user@example.com']);
    $this->post('/newsletter/subscribe', ['email' => 'user@example.com']);

    expect(NewsletterConfirmation::where('email', 'user@example.com')->count())->toBe(1);
    Mail::assertSent(ConfirmationMail::class, 2);
});

test('an expired pending confirmation gets a new uuid', function () {
    Mail::fake();

    $this->post('/newsletter/subscribe', ['email' => 'user@example.com']);
    $first = NewsletterConfirmation::first();

    NewsletterConfirmation::where('id', $first->id)
        ->update(['created_at' => now()->subHours(25)]);

    $this->post('/newsletter/subscribe', ['email' => 'user@example.com']);

    expect(NewsletterConfirmation::where('email', 'user@example.com')->count())->toBe(2);

    $latest = NewsletterConfirmation::latest('id')->first();
    expect($latest->uuid)->not->toBe($first->uuid);
});

test('subscribing an already confirmed email reports already_confirmed without sending', function () {
    Mail::fake();

    NewsletterConfirmation::create([
        'uuid' => (string) Str::uuid(),
        'email' => 'user@example.com',
        'is_confirmed' => true,
        'date_confirmed' => now(),
    ]);

    $response = $this->from('/index')->post('/newsletter/subscribe', [
        'email' => 'user@example.com',
    ]);

    $response->assertSessionHas('newsletter', [
        'status' => 'already_confirmed',
        'email' => 'user@example.com',
    ]);
    Mail::assertNotSent(ConfirmationMail::class);
});

test('send cancel link sends the email in the subscriber language', function () {
    Mail::fake();

    NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'User',
        'email' => 'user@example.com',
        'lang' => 'fr',
        'is_canceled' => false,
    ]);

    $response = $this->from('/index')->post('/newsletter/send-cancel-link', [
        'email' => 'user@example.com',
    ]);

    $response->assertSessionHas('newsletter', ['status' => 'cancel_link_sent']);
    Mail::assertSent(CancelLinkMail::class, function (CancelLinkMail $mail) {
        return $mail->lang === 'fr';
    });
});

test('send cancel link for an unknown email answers generically', function () {
    Mail::fake();

    $response = $this->from('/index')->post('/newsletter/send-cancel-link', [
        'email' => 'ghost@example.com',
    ]);

    $response->assertSessionHas('newsletter', ['status' => 'cancel_link_sent']);
    Mail::assertNotSent(CancelLinkMail::class);
});

test('subscribe with an invalid email fails validation', function () {
    Mail::fake();

    $response = $this->from('/index')->post('/newsletter/subscribe', [
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors('email');
    Mail::assertNothingSent();
});
