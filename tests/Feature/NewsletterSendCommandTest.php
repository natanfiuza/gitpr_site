<?php

use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use App\Support\NewsletterContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    File::ensureDirectoryExists(public_path('content/newsletter/test-v1'));
    File::put(public_path('content/newsletter/test-v1/newsletter_body.md'), "# EN body\n\nHello in English.");
    File::put(public_path('content/newsletter/test-v1/newsletter_body.pt_br.md'), "# Corpo PT\n\nOlá em português.");
});

afterEach(function () {
    File::deleteDirectory(public_path('content/newsletter/test-v1'));
});

function make_newsletter_subscriber(string $email, string $lang = 'en'): NewsletterSubscriber
{
    return NewsletterSubscriber::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'User',
        'email' => $email,
        'lang' => $lang,
        'is_canceled' => false,
    ]);
}

test('newsletter send uses the body in each subscriber language', function () {
    Mail::fake();

    make_newsletter_subscriber('en@example.com', 'en');
    make_newsletter_subscriber('pt@example.com', 'pt_br');

    $this->artisan('newsletter:send', ['version' => 'test-v1', '--interval' => 0])
        ->assertSuccessful();

    Mail::assertSent(NewsletterMail::class, 2);

    $bodies = Mail::sent(NewsletterMail::class)->map(fn ($mail) => $mail->htmlBody);
    expect($bodies->contains(fn ($body) => str_contains($body, 'Hello in English')))->toBeTrue();
    expect($bodies->contains(fn ($body) => str_contains($body, 'Olá em português')))->toBeTrue();
});

test('subscribers without a language file fall back to the english body', function () {
    Mail::fake();

    make_newsletter_subscriber('fr@example.com', 'fr');

    $this->artisan('newsletter:send', ['version' => 'test-v1', '--interval' => 0])
        ->assertSuccessful();

    Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) {
        return str_contains($mail->htmlBody, 'Hello in English');
    });
});

test('the same version is not sent twice without force', function () {
    Mail::fake();
    make_newsletter_subscriber('en@example.com', 'en');

    $this->artisan('newsletter:send', ['version' => 'test-v1', '--interval' => 0])
        ->assertSuccessful();

    Mail::fake(); // Reset the sent counter

    $this->artisan('newsletter:send', ['version' => 'test-v1', '--interval' => 0])
        ->assertSuccessful();
    Mail::assertNothingSent();

    $this->artisan('newsletter:send', ['version' => 'test-v1', '--interval' => 0, '--force' => true])
        ->assertSuccessful();
    Mail::assertSent(NewsletterMail::class, 1);
});

test('large volumes abort without force and warn about duration', function () {
    Mail::fake();

    for ($i = 0; $i < 721; $i++) {
        make_newsletter_subscriber("user{$i}@example.com", 'en');
    }

    $this->artisan('newsletter:send', ['version' => 'test-v1'])
        ->expectsOutputToContain('Use --force to proceed.')
        ->assertFailed();

    Mail::assertNothingSent();
});

test('the version defaults to the GitPR version from the current report', function () {
    expect(NewsletterContent::version_from_relatorio())->toMatch('/^\d+\.\d+\.\d+$/');
});

test('fails fast when the body does not exist for the resolved version', function () {
    $this->artisan('newsletter:send', ['version' => 'no-such-version', '--interval' => 0])
        ->expectsOutputToContain('Newsletter body not found for version')
        ->assertFailed();
});
