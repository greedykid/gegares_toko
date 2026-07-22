<?php

namespace App\Livewire\Admin;

use App\Support\IntegrationSettings;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Edits the external service credentials from the admin panel.
 *
 * Secrets are write-only: a stored key is never sent back to the browser, the
 * field simply shows that one is saved. Leaving it blank keeps what is there,
 * so the page can be opened and saved without ever putting a live API key into
 * the HTML.
 */
class ManageIntegrations extends Component
{
    /** @var array<string, string|null> */
    public array $form = [];

    /** @var array<string, bool> which secrets already have a stored value */
    public array $stored = [];

    public function mount(): void
    {
        $this->refreshForm();
    }

    protected function refreshForm(): void
    {
        $this->form = [];
        $this->stored = [];

        foreach (IntegrationSettings::fields() as $key => [$path, $label, $secret]) {
            if ($secret) {
                // Never hydrate a secret into the component state.
                $this->form[$key] = '';
                $this->stored[$key] = filled(IntegrationSettings::get($key));

                continue;
            }

            // Non-secrets show the value actually in force, .env included, so the
            // admin sees what the app is really using.
            $this->form[$key] = IntegrationSettings::get($key) ?? (string) config($path);
        }
    }

    public function save(): void
    {
        $this->validate([
            'form.google_redirect' => 'nullable|url',
            'form.ai_base_url' => 'nullable|url',
            'form.mail_port' => 'nullable|integer|min:1|max:65535',
            'form.mail_from_address' => 'nullable|email',
            'form.mail_mailer' => 'nullable|in:smtp,log,array,sendmail',
            'form.mail_scheme' => 'nullable|in:smtp,smtps',
        ], [], [
            'form.google_redirect' => 'Redirect URL',
            'form.ai_base_url' => 'Base URL',
            'form.mail_port' => 'Port',
            'form.mail_from_address' => 'Email pengirim',
            'form.mail_mailer' => 'Driver email',
            'form.mail_scheme' => 'Enkripsi',
        ]);

        IntegrationSettings::put($this->form);

        // Value itself is never logged — only that it changed, and by whom.
        Log::info('Integration credentials updated by admin #'.auth()->id());

        $this->refreshForm();

        $this->dispatch('toast', type: 'success', message: 'Pengaturan integrasi berhasil disimpan.');
    }

    /** Drop a stored credential so the .env value takes over again. */
    public function clearCredential(string $key): void
    {
        if (! array_key_exists($key, IntegrationSettings::fields())) {
            return;
        }

        IntegrationSettings::forget($key);
        $this->refreshForm();

        $this->dispatch('toast', type: 'success', message: 'Kredensial dihapus, nilai dari file .env kembali dipakai.');
    }

    public function render()
    {
        return view('livewire.admin.manage-integrations', [
            'groups' => IntegrationSettings::groups(),
        ]);
    }
}
