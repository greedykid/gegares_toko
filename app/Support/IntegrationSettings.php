<?php

namespace App\Support;

use App\Models\AppCredential;

/**
 * Bridges the admin-managed credentials onto Laravel's config.
 *
 * Anything stored here overrides the matching config value at boot; a blank or
 * missing row leaves whatever .env provided. That keeps .env authoritative for
 * anyone who can reach the file, and gives the panel a way in for a host where
 * that is impractical.
 *
 * Values are deliberately NOT written to the cache store: the cache driver is
 * the database in production, so caching them would put the very plaintext the
 * `encrypted` cast exists to avoid back into a table. One small query per
 * request, memoised for the rest of it, is the cheaper trade.
 */
class IntegrationSettings
{
    /** @var array<string, string|null>|null */
    protected static ?array $memo = null;

    /**
     * Every editable credential: storage key => [config path, label, secret?].
     *
     * "secret" drives the UI — those are never rendered back into the form, and
     * submitting the field blank means "leave what is already stored".
     *
     * @return array<string, array<string, array{0: string, 1: string, 2: bool}>>
     */
    public static function groups(): array
    {
        return [
            'google' => [
                'google_client_id' => ['services.google.client_id', 'Client ID', false],
                'google_client_secret' => ['services.google.client_secret', 'Client Secret', true],
                'google_redirect' => ['services.google.redirect', 'Redirect URL', false],
            ],
            'pakasir' => [
                'pakasir_project_slug' => ['pakasir.project_slug', 'Project Slug', false],
                'pakasir_api_key' => ['pakasir.api_key', 'API Key', true],
            ],
            'biteship' => [
                'biteship_api_key' => ['biteship.api_key', 'API Key', true],
                'biteship_webhook_token' => ['biteship.webhook_token', 'Webhook Token', true],
                'biteship_origin_area_id' => ['biteship.origin_area_id', 'Origin Area ID', false],
            ],
            'ai' => [
                'ai_key' => ['services.ai.key', 'API Key', true],
                'ai_base_url' => ['services.ai.base_url', 'Base URL', false],
                'ai_model' => ['services.ai.model', 'Model', false],
            ],
            'mail' => [
                'mail_mailer' => ['mail.default', 'Driver', false],
                'mail_host' => ['mail.mailers.smtp.host', 'Host', false],
                'mail_port' => ['mail.mailers.smtp.port', 'Port', false],
                'mail_scheme' => ['mail.mailers.smtp.scheme', 'Enkripsi', false],
                'mail_username' => ['mail.mailers.smtp.username', 'Username', false],
                'mail_password' => ['mail.mailers.smtp.password', 'Password', true],
                'mail_from_address' => ['mail.from.address', 'Email Pengirim', false],
                'mail_from_name' => ['mail.from.name', 'Nama Pengirim', false],
            ],
        ];
    }

    /** Flat map of key => [config path, label, secret?]. */
    public static function fields(): array
    {
        return array_merge(...array_values(static::groups()));
    }

    public static function isSecret(string $key): bool
    {
        return static::fields()[$key][2] ?? false;
    }

    /**
     * Stored values, keyed by credential key. Memoised per request.
     *
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        try {
            static::$memo = AppCredential::query()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (AppCredential $c) => [$c->key => $c->value])
                ->all();
        } catch (\Throwable) {
            // Table not migrated yet (fresh install, or running migrations):
            // fall back to .env rather than taking the app down.
            static::$memo = [];
        }

        return static::$memo;
    }

    public static function get(string $key): ?string
    {
        $value = static::all()[$key] ?? null;

        return $value === '' ? null : $value;
    }

    /** Overlay the stored credentials onto config. Called from AppServiceProvider. */
    public static function applyToConfig(): void
    {
        $fields = static::fields();

        foreach (static::all() as $key => $value) {
            if ($value === null || $value === '') {
                continue; // leave the .env value in place
            }

            if (isset($fields[$key])) {
                config([$fields[$key][0] => $value]);
            }
        }
    }

    /**
     * Persist submitted values. A blank secret is treated as "unchanged" so the
     * form never has to echo a stored key back to the browser to keep it.
     *
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        $fields = static::fields();

        foreach ($values as $key => $value) {
            if (! isset($fields[$key])) {
                continue;
            }

            $value = is_string($value) ? trim($value) : $value;

            if (static::isSecret($key) && ($value === null || $value === '')) {
                continue;
            }

            AppCredential::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        static::$memo = null;
    }

    /** Drop a stored credential so the .env value takes over again. */
    public static function forget(string $key): void
    {
        AppCredential::where('key', $key)->delete();
        static::$memo = null;
    }

    /** Testing seam. */
    public static function flushMemo(): void
    {
        static::$memo = null;
    }
}
