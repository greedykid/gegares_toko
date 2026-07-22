<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validation messages reach customers, so they are in Indonesian like the rest
 * of the storefront. The translations come from laravel-lang, but the field
 * names in them do not: nothing in that package knows what `address_id` or
 * `shipping_courier` mean here, so lang/id/validation.php carries a local
 * `attributes` block for them.
 *
 * That block is the fragile part — `php artisan lang:update` rewrites the file
 * from the package and would silently drop it, leaving customers with "Address
 * id wajib diisi". These tests are what turns that from a comment into a
 * failure.
 */
class ValidationLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_are_in_indonesian(): void
    {
        $this->assertEquals(
            'Nama wajib diisi.',
            validator([], ['name' => 'required'])->errors()->first()
        );

        $this->assertEquals(
            'Kata sandi minimal berisi 8 karakter.',
            validator(['password' => '123'], ['password' => 'min:8'])->errors()->first()
        );
    }

    public function test_the_shop_own_field_names_are_translated(): void
    {
        // Each of these would otherwise read as its raw column name.
        $cases = [
            ['address_id', 'required', 'Alamat pengiriman wajib diisi.'],
            ['shipping_courier', 'required', 'Kurir pengiriman wajib diisi.'],
            ['shipping_service', 'required', 'Layanan pengiriman wajib diisi.'],
            ['stock', 'integer', 'Stok harus berupa bilangan bulat.'],
            ['min_purchase', 'numeric', 'Minimal belanja harus berupa angka.'],
            ['tracking_number', 'required', 'Nomor resi wajib diisi.'],
        ];

        foreach ($cases as [$field, $rule, $expected]) {
            $this->assertEquals(
                $expected,
                validator([$field => $rule === 'required' ? null : 'bukan-angka'], [$field => $rule])
                    ->errors()->first($field),
                "The Indonesian attribute name for '{$field}' is missing."
            );
        }
    }

    public function test_customers_are_told_about_email_not_surel(): void
    {
        // laravel-lang uses the formal "surel"; shoppers say "email".
        $message = validator(['email' => 'bukan-email'], ['email' => 'email'])->errors()->first();

        $this->assertStringContainsString('email', $message);
        $this->assertStringNotContainsString('surel', $message);
    }

    public function test_a_real_form_submission_answers_in_indonesian(): void
    {
        // Through the HTTP stack rather than the validator alone, so the
        // response the customer actually receives is what is checked.
        $this->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
    }

    public function test_the_recaptcha_field_is_not_shown_by_its_raw_name(): void
    {
        // The login form validates 'g-recaptcha-response', which without a
        // translation greets the customer as "G-recaptcha-response wajib diisi."
        $this->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['g-recaptcha-response' => 'Verifikasi keamanan wajib diisi.']);
    }

    public function test_auth_failure_is_also_translated(): void
    {
        User::factory()->create(['email' => 'ada@gegares.test', 'password' => bcrypt('rahasia-betul')]);

        // The token is only checked when reCAPTCHA keys are configured; the
        // field is required regardless, so it has to be present to reach the
        // credential check at all.
        $this->post(route('login'), [
            'email' => 'ada@gegares.test',
            'password' => 'salah',
            'g-recaptcha-response' => 'test-token',
        ])->assertSessionHasErrors('email');

        $this->assertStringNotContainsString(
            'These credentials do not match',
            session('errors')->first('email')
        );
    }
}
