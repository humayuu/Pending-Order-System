<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_with_username_and_log_out(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', ['username' => 'admin', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_wrong_password_or_email_login_is_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', ['username' => 'admin', 'password' => 'nope'])->assertSessionHasErrors('username');
        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_seeding_twice_updates_the_same_admin_and_hashes_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertNotSame('password', User::first()->password);
    }
}
