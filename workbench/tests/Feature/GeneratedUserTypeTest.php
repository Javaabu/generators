<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The user type generate:auth wrote works with javaabu/auth: its guard,
 * broker and routes are registered, and its users can log in.
 */
class GeneratedUserTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_the_user_type(): void
    {
        $type = UserTypes::get('customer');

        $this->assertEquals('customer', $type->guardName());
        $this->assertEquals(Customer::class, $type->modelClass());
        $this->assertEquals('customer', UserTypes::for(new Customer)->name());
    }

    #[Test]
    public function it_shows_the_login_page_of_the_user_type(): void
    {
        $this->get(route('customer.login'))
            ->assertOk();
    }

    #[Test]
    public function it_logs_customers_in_on_their_guard(): void
    {
        $customer = Customer::factory()->create(['email' => 'ali@example.com']);

        $this->post(route('customer.login.store'), [
            'email' => 'ali@example.com',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($customer, 'customer');
        $this->assertGuest('web');
    }
}
