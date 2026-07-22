<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageIntegrations;
use App\Models\AppCredential;
use App\Models\User;
use App\Support\IntegrationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Credentials the admin can edit override .env, are encrypted at rest, and are
 * never rendered back into the page.
 */
class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        IntegrationSettings::flushMemo();
    }

    public function test_a_stored_value_is_encrypted_at_rest(): void
    {
        IntegrationSettings::put(['pakasir_api_key' => 'super-secret-key']);

        $raw = DB::table('app_credentials')->where('key', 'pakasir_api_key')->value('value');

        $this->assertNotSame('super-secret-key', $raw, 'The key was written in plaintext.');
        $this->assertStringNotContainsString('super-secret-key', $raw);

        // Still readable through the model.
        $this->assertSame('super-secret-key', IntegrationSettings::get('pakasir_api_key'));
    }

    public function test_a_stored_value_overrides_the_env_config(): void
    {
        config(['pakasir.api_key' => 'from-env']);

        IntegrationSettings::put(['pakasir_api_key' => 'from-database']);
        IntegrationSettings::applyToConfig();

        $this->assertSame('from-database', config('pakasir.api_key'));
    }

    public function test_recaptcha_keys_can_be_managed_and_override_env(): void
    {
        config(['services.recaptcha.site' => 'env-site', 'services.recaptcha.secret' => 'env-secret']);

        IntegrationSettings::put([
            'recaptcha_site_key' => 'db-site',
            'recaptcha_secret_key' => 'db-secret',
        ]);
        IntegrationSettings::applyToConfig();

        $this->assertSame('db-site', config('services.recaptcha.site'));
        $this->assertSame('db-secret', config('services.recaptcha.secret'));

        // The secret is encrypted at rest like the other secret fields.
        $raw = DB::table('app_credentials')->where('key', 'recaptcha_secret_key')->value('value');
        $this->assertStringNotContainsString('db-secret', (string) $raw);
    }

    public function test_a_blank_row_leaves_the_env_value_alone(): void
    {
        config(['biteship.api_key' => 'from-env']);

        AppCredential::create(['key' => 'biteship_api_key', 'value' => '']);
        IntegrationSettings::flushMemo();
        IntegrationSettings::applyToConfig();

        $this->assertSame('from-env', config('biteship.api_key'));
    }

    public function test_clearing_a_credential_hands_back_to_env(): void
    {
        config(['services.ai.key' => 'from-env']);
        IntegrationSettings::put(['ai_key' => 'from-database']);

        IntegrationSettings::forget('ai_key');
        IntegrationSettings::applyToConfig();

        $this->assertSame('from-env', config('services.ai.key'));
    }

    public function test_the_form_never_renders_a_stored_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        IntegrationSettings::put(['mail_password' => 'smtp-p4ssw0rd']);

        $this->actingAs($admin);

        Livewire::test(ManageIntegrations::class)
            ->assertSet('form.mail_password', '')
            ->assertSet('stored.mail_password', true)
            ->assertDontSee('smtp-p4ssw0rd');
    }

    public function test_submitting_a_blank_secret_keeps_the_stored_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        IntegrationSettings::put(['mail_password' => 'smtp-p4ssw0rd']);

        $this->actingAs($admin);

        // Saving the page without retyping the password must not wipe it.
        Livewire::test(ManageIntegrations::class)
            ->set('form.mail_host', 'smtp.example.com')
            ->call('save');

        IntegrationSettings::flushMemo();
        $this->assertSame('smtp-p4ssw0rd', IntegrationSettings::get('mail_password'));
        $this->assertSame('smtp.example.com', IntegrationSettings::get('mail_host'));
    }

    public function test_a_new_secret_replaces_the_old_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        IntegrationSettings::put(['mail_password' => 'old-password']);

        $this->actingAs($admin);

        Livewire::test(ManageIntegrations::class)
            ->set('form.mail_password', 'new-password')
            ->call('save');

        IntegrationSettings::flushMemo();
        $this->assertSame('new-password', IntegrationSettings::get('mail_password'));
    }

    public function test_invalid_values_are_refused(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(ManageIntegrations::class)
            ->set('form.mail_from_address', 'bukan-email')
            ->set('form.mail_port', 99999)
            ->call('save')
            ->assertHasErrors(['form.mail_from_address', 'form.mail_port']);
    }

    public function test_the_integrations_tab_is_reachable_from_content_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.content'))
            ->assertOk()
            ->assertSee('Integrasi');
    }

    public function test_a_customer_cannot_reach_the_settings_page(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'phone' => '081234567890']);

        $this->actingAs($customer)
            ->get(route('admin.settings.content'))
            ->assertRedirect();
    }
}
