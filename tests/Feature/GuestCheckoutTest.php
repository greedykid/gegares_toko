<?php

namespace Tests\Feature;

use App\Livewire\GuestAddressForm;
use App\Livewire\SelectShipping;
use App\Models\Order;
use App\Models\User;
use App\Services\BiteshipService;
use App\Services\CartService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** A CartService test double with one item and clean stock. */
    protected function fakeCartWithItem(): void
    {
        $cart = Mockery::mock(CartService::class);
        $cart->shouldReceive('getItems')->andReturn(['1_0' => [
            'id' => '1_0', 'product_id' => 1, 'variant_id' => null, 'name' => 'Klepon',
            'price' => 10000, 'image' => null, 'slug' => 'klepon', 'quantity' => 1, 'stock' => 10,
        ]]);
        $cart->shouldReceive('validateStock')->andReturn([]);
        $this->instance(CartService::class, $cart);
    }

    /** A guest with an empty cart is bounced off the checkout page. */
    public function test_guest_checkout_redirects_when_cart_empty(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('products.index'));
    }

    /** The navbar cart components must mount for guests, otherwise the
     *  add-to-cart button has no listener and hangs on its loading state. */
    public function test_cart_components_render_for_guests(): void
    {
        Livewire::test(\App\Livewire\CartIcon::class)->assertOk();
        Livewire::test(\App\Livewire\CartDrawer::class)->assertOk();
    }

    /** GuestAddressForm stashes the address in the session (no DB, no Biteship). */
    public function test_guest_address_form_saves_to_session(): void
    {
        Livewire::test(GuestAddressForm::class)
            ->set('recipient_name', 'Budi')
            ->set('phone', '08123456789')
            ->set('label', 'Rumah')
            ->set('area_id', 'IDNP6IDNC148IDND836')
            ->set('city', 'Jakarta Selatan')
            ->set('province', 'DKI Jakarta')
            ->set('postal_code', '12810')
            ->set('address_line', 'Jl. Tebet Barat No. 1')
            ->set('latitude', -6.23)
            ->set('longitude', 106.85)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('saved', true)
            ->assertDispatched('guestAddressUpdated');

        $this->assertSame('IDNP6IDNC148IDND836', session('checkout.guest_address.area_id'));
        $this->assertSame('Budi', session('checkout.guest_address.recipient_name'));
    }

    /** A guest sees courier rates quoted from their session address, even
     *  though there is no DB address (selectedAddressId stays null). */
    public function test_guest_shipping_quotes_from_session_address(): void
    {
        session([
            'cart' => ['1_0' => [
                'id' => '1_0', 'product_id' => 1, 'variant_id' => null, 'name' => 'Klepon',
                'price' => 10000, 'image' => null, 'slug' => 'klepon', 'quantity' => 1, 'stock' => 10,
            ]],
            'checkout.guest_address' => ['area_id' => 'IDNP6IDNC148IDND836', 'latitude' => -6.23, 'longitude' => 106.85],
        ]);

        $biteship = Mockery::mock(BiteshipService::class);
        $biteship->shouldReceive('getShippingRates')->andReturn([[
            'courier_code' => 'grab', 'courier_service_code' => 'instant', 'price' => 15000,
            'courier_name' => 'Grab', 'courier_service_name' => 'Instant',
            'shipment_duration_range' => '1-2', 'shipment_duration_unit' => 'jam',
        ]]);
        $this->instance(BiteshipService::class, $biteship);

        Livewire::test(SelectShipping::class)
            ->assertViewHas('hasAddress', true)
            ->assertSee('Grab');
    }

    /** An admin who reaches resume (via a leftover guest checkout) must NOT get
     *  a customer order created for them — they go to the dashboard instead. */
    public function test_resume_does_not_place_order_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '08123456789']);

        session([
            'cart' => ['1_0' => [
                'id' => '1_0', 'product_id' => 1, 'variant_id' => null, 'name' => 'Klepon',
                'price' => 10000, 'image' => null, 'slug' => 'klepon', 'quantity' => 1, 'stock' => 10,
            ]],
            'checkout.pending' => ['shipping_courier' => 'grab', 'shipping_service' => 'instant', 'payment_method' => 'pakasir'],
            'checkout.guest_address' => ['area_id' => 'IDNP6IDNC148IDND836'],
        ]);

        $this->actingAs($admin)
            ->get(route('checkout.resume'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame(0, Order::count());
    }

    /** Missing required fields keep the form open and stash nothing. */
    public function test_guest_address_form_validates(): void
    {
        Livewire::test(GuestAddressForm::class)
            ->set('recipient_name', '')
            ->call('save')
            ->assertHasErrors(['recipient_name', 'area_id', 'address_line']);

        $this->assertNull(session('checkout.guest_address'));
    }

    /** guestSubmit refuses to proceed until the address step is done. */
    public function test_guest_submit_requires_address(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->fakeCartWithItem();

        $this->from(route('checkout.index'))
            ->post(route('checkout.guestSubmit'), [
                'shipping_courier' => 'grab',
                'shipping_service' => 'instant',
                'payment_method' => 'pakasir',
                'shipping_cost' => 15000,
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertNull(session('checkout.pending'));
    }

    /** With an address stashed, guestSubmit saves pending and sends to login. */
    public function test_guest_submit_stashes_pending_and_redirects_to_login(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->fakeCartWithItem();
        session(['checkout.guest_address' => ['area_id' => 'IDNP6IDNC148IDND836']]);

        $this->post(route('checkout.guestSubmit'), [
            'shipping_courier' => 'grab',
            'shipping_service' => 'instant',
            'payment_method' => 'pakasir',
            'shipping_cost' => 15000,
            'notes' => 'Jangan pedas',
        ])->assertRedirect(route('login'));

        $this->assertSame('grab', session('checkout.pending.shipping_courier'));
        $this->assertSame('Jangan pedas', session('checkout.pending.notes'));
        $this->assertSame(route('checkout.resume'), session('url.intended'));
    }
}
