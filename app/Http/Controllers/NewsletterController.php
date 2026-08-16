<?php

namespace App\Http\Controllers;

use App\Mail\CancelLinkMail;
use App\Mail\ConfirmationMail;
use App\Models\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use App\Support\NewsletterTranslations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    /**
     * Resolve a valid language from the request, falling back to English.
     */
    private function lang(Request $request, string $default = 'en'): string
    {
        $lang = $request->input('lang', $request->query('lang', $default));

        return in_array($lang, NewsletterTranslations::LANGS, true) ? $lang : 'en';
    }

    /**
     * Subscribe an email: sends a confirmation link (reusing the uuid of a
     * pending, non-expired record when the email is submitted again).
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $lang = $this->lang($request);
        $email = $validated['email'];

        $already_confirmed = NewsletterConfirmation::query()
            ->where('email', $email)
            ->where('is_confirmed', true)
            ->exists();

        if ($already_confirmed) {
            return back()->with('newsletter', ['status' => 'already_confirmed', 'email' => $email]);
        }

        $pending = NewsletterConfirmation::query()
            ->where('email', $email)
            ->where('is_confirmed', false)
            ->notExpired()
            ->first();

        if ($pending) {
            $confirmation = $pending; // Reuse the same uuid while it is valid
        } else {
            $confirmation = NewsletterConfirmation::create([
                'uuid' => (string) Str::uuid(),
                'email' => $email,
                'is_confirmed' => false,
            ]);
        }

        Mail::to($email)->send(new ConfirmationMail(
            url: route('newsletter.confirm', ['uuid' => $confirmation->uuid, 'lang' => $lang]),
            strings: NewsletterTranslations::for($lang),
            lang: $lang,
        ));

        return back()->with('newsletter', ['status' => 'sent']);
    }

    /**
     * Send a cancellation link to a registered (active) subscriber.
     * Always answers generically so email existence is not leaked.
     */
    public function send_cancel_link(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::query()
            ->where('email', $validated['email'])
            ->active()
            ->first();

        if ($subscriber) {
            Mail::to($subscriber->email)->send(new CancelLinkMail(
                url: route('newsletter.cancel', ['uuid' => $subscriber->uuid]),
                strings: NewsletterTranslations::for($subscriber->lang),
                lang: $subscriber->lang,
            ));
        }

        return back()->with('newsletter', ['status' => 'cancel_link_sent']);
    }

    /**
     * Confirmation screen: shows the profile form, or a status panel for
     * expired, already-confirmed and unknown links.
     */
    public function confirm(Request $request, string $uuid)
    {
        $confirmation = NewsletterConfirmation::query()->where('uuid', $uuid)->first();

        if (! $confirmation) {
            $status = 'not_found';
            $email = null;
        } elseif ($confirmation->is_confirmed) {
            $status = 'already_confirmed';
            $email = $confirmation->email;
        } elseif ($confirmation->created_at->lt(now()->subHours(24))) {
            $status = 'expired';
            $email = $confirmation->email;
        } else {
            $status = 'form';
            $email = $confirmation->email;
        }

        $subscriber = $email
            ? NewsletterSubscriber::query()->where('email', $email)->first()
            : null;

        $lang = $this->lang($request, $subscriber?->lang ?? 'en');

        return Inertia::render('NewsletterConfirmPage', [
            'status' => $status,
            'uuid' => $uuid,
            'email' => $email,
            'subscriber' => $subscriber?->only(['name', 'github', 'phone', 'lang']),
            'current_lang' => $lang,
            'ui_strings' => NewsletterTranslations::for($lang),
        ]);
    }

    /**
     * Persist the profile data and mark the confirmation record as confirmed.
     * Re-activates the subscription when the subscriber was canceled before.
     */
    public function confirm_submit(Request $request, string $uuid)
    {
        $confirmation = NewsletterConfirmation::query()->where('uuid', $uuid)->first();

        if (! $confirmation) {
            return redirect()->route('newsletter.confirm', ['uuid' => $uuid]);
        }

        if ($confirmation->is_confirmed || $confirmation->created_at->lt(now()->subHours(24))) {
            return redirect()->route('newsletter.confirm', ['uuid' => $uuid]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'lang' => ['required', 'string', 'in:'.implode(',', NewsletterTranslations::LANGS)],
        ]);

        if ($validated['email'] !== $confirmation->email) {
            abort(422, 'Email does not match the confirmation link.');
        }

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $confirmation->email]);
        $subscriber->uuid = $subscriber->uuid ?? (string) Str::uuid();
        $subscriber->name = $validated['name'];
        $subscriber->github = $validated['github'] ?? null;
        $subscriber->phone = $validated['phone'] ?? null;
        $subscriber->lang = $validated['lang'];
        $subscriber->is_canceled = false; // Re-activates canceled subscriptions
        $subscriber->date_canceled = null;
        $subscriber->save();

        // "Only one confirmed record per email" guard.
        $already_confirmed = NewsletterConfirmation::query()
            ->where('email', $confirmation->email)
            ->where('is_confirmed', true)
            ->where('id', '!=', $confirmation->id)
            ->exists();

        if (! $already_confirmed) {
            $confirmation->update([
                'is_confirmed' => true,
                'date_confirmed' => now(),
            ]);
        }

        return redirect()->route('newsletter.confirm', ['uuid' => $uuid, 'lang' => $validated['lang']]);
    }

    /**
     * Cancellation screen: asks for confirmation (or shows the result).
     */
    public function cancel(Request $request, string $uuid)
    {
        $subscriber = NewsletterSubscriber::query()->where('uuid', $uuid)->first();

        $lang = $this->lang($request, $subscriber?->lang ?? 'en');

        if (! $subscriber) {
            $status = 'not_found';
        } elseif ($request->boolean('done')) {
            $status = 'done';
        } elseif ($subscriber->is_canceled) {
            $status = 'already_canceled';
        } else {
            $status = 'form';
        }

        return Inertia::render('NewsletterCancelPage', [
            'status' => $status,
            'uuid' => $uuid,
            'current_lang' => $lang,
            'ui_strings' => NewsletterTranslations::for($lang),
        ]);
    }

    /**
     * Execute the cancellation (RFC 8058 one-click POST endpoint).
     */
    public function unsubscribe(string $uuid)
    {
        $subscriber = NewsletterSubscriber::query()->where('uuid', $uuid)->first();

        if ($subscriber) {
            $subscriber->update([
                'is_canceled' => true,
                'date_canceled' => now(),
            ]);
        }

        return redirect()->route('newsletter.cancel', [
            'uuid' => $uuid,
            'lang' => $subscriber?->lang ?? 'en',
            'done' => 1,
        ]);
    }
}
