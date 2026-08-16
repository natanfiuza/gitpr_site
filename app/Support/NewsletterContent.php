<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Reads and versions the newsletter content stored under
 * public/content/newsletter/{version}/newsletter_body.{lang}.md.
 */
final class NewsletterContent
{
    /**
     * Extract the report version from the H1 of public/content/relatorio.md.
     * Example: "# Project Status Report: GitPR CLI — v0.0.10 (2026-08-11)" → "v0.0.10".
     */
    public static function version_from_relatorio(): ?string
    {
        $path = public_path('content/relatorio.md');

        if (! File::exists($path)) {
            return null;
        }

        $first_line = strtok(File::get($path), "\n");

        if ($first_line === false || ! preg_match('/v(\d+\.\d+\.\d+)/', $first_line, $matches)) {
            return null;
        }

        return 'v'.$matches[1];
    }

    /**
     * Load the newsletter body markdown for the given language, falling back
     * to English when the requested language file does not exist.
     *
     * @throws RuntimeException when no body exists for the version at all.
     */
    public static function body_markdown(string $version, string $lang): string
    {
        $base = public_path("content/newsletter/{$version}/newsletter_body");

        $path = $lang === 'en' ? "{$base}.md" : "{$base}.{$lang}.md";

        if (! File::exists($path) && $lang !== 'en') {
            $path = "{$base}.md"; // Fallback to English
        }

        if (! File::exists($path)) {
            throw new RuntimeException("Newsletter body not found for version {$version}.");
        }

        return File::get($path);
    }

    /**
     * Convert the newsletter body markdown to HTML (GFM).
     */
    public static function body_html(string $version, string $lang): string
    {
        return Str::markdown(self::body_markdown($version, $lang));
    }

    /**
     * Version of the last newsletter sent (marker file), if any.
     */
    public static function last_sent_version(): ?string
    {
        if (! Storage::disk('local')->exists('newsletter/last_sent.txt')) {
            return null;
        }

        return trim(Storage::disk('local')->get('newsletter/last_sent.txt')) ?: null;
    }

    /**
     * Record the version as sent, preventing accidental re-sends.
     */
    public static function mark_sent(string $version): void
    {
        Storage::disk('local')->put('newsletter/last_sent.txt', $version);
    }
}
