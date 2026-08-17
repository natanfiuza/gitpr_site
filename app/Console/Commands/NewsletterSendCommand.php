<?php

namespace App\Console\Commands;

use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use App\Support\NewsletterContent;
use App\Support\NewsletterTranslations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class NewsletterSendCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'newsletter:send
                            {version? : Newsletter version (defaults to the GitPR version from the current report)}
                            {--force : Skip the already-sent and long-run warnings}
                            {--interval=5 : Seconds between sends}';

    /**
     * @var string
     */
    protected $description = 'Send the newsletter of a given version to all active subscribers.';

    public function handle(): int
    {
        $version = $this->argument('version') ?: NewsletterContent::version_from_relatorio();

        if (! $version) {
            $this->error('Could not extract the version from public/content/relatorio.md. Use --version.');

            return self::FAILURE;
        }

        if (NewsletterContent::last_sent_version() === $version && ! $this->option('force')) {
            $this->info("Newsletter {$version} was already sent. Use --force to send it again.");

            return self::SUCCESS;
        }

        // Fail fast when the body does not exist, instead of erroring per subscriber.
        try {
            NewsletterContent::body_markdown($version, 'en');
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $subscribers = NewsletterSubscriber::active()->get();

        if ($subscribers->isEmpty()) {
            $this->info('No active subscribers.');

            return self::SUCCESS;
        }

        $interval = (int) $this->option('interval');
        $estimated_seconds = $subscribers->count() * max(0, $interval);

        if ($estimated_seconds > 3600 && ! $this->option('force')) {
            $this->warn(sprintf(
                'Sending %d emails will take about %.1f hours. Use --force to proceed.',
                $subscribers->count(),
                $estimated_seconds / 3600
            ));

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($subscribers->count());

        foreach ($subscribers as $subscriber) {
            try {
                $html = NewsletterContent::body_html($version, $subscriber->lang);

                Mail::to($subscriber->email)->send(new NewsletterMail(
                    htmlBody: $html,
                    version: $version,
                    unsubscribeUrl: route('newsletter.cancel', ['uuid' => $subscriber->uuid]),
                    strings: NewsletterTranslations::for($subscriber->lang),
                    lang: $subscriber->lang,
                ));
            } catch (Throwable $e) {
                // A single failure must not interrupt the batch.
                $this->warn("Failed to send to {$subscriber->email}: {$e->getMessage()}");
            }

            $bar->advance();

            if ($interval > 0) {
                sleep($interval);
            }
        }

        $bar->finish();
        $this->newLine();

        NewsletterContent::mark_sent($version);

        $this->info("Newsletter {$version} sent to {$subscribers->count()} subscriber(s).");

        return self::SUCCESS;
    }
}
