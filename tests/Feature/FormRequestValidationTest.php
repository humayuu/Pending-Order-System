<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_username_and_password(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
    }

    public function test_client_validation_and_update_keeps_own_name(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['name' => 'Acme']);

        $this->post('/clients', ['name' => '', 'address' => ''])->assertSessionHasErrors(['name', 'address']);
        $this->put("/clients/{$client->id}", ['name' => 'Acme', 'address' => 'Street 1'])
            ->assertSessionDoesntHaveErrors()->assertRedirect('/clients');
    }

    public function test_order_store_saves_line_image_and_rejects_non_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $line = ['item_name' => 'Bolt', 'quantity' => 5];

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => [$line + ['file' => UploadedFile::fake()->create('x.exe', 10, 'application/octet-stream')]]])
            ->assertSessionHasErrors('lines.0.file');

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => [$line + ['file' => UploadedFile::fake()->image('bolt.jpg')]]])
            ->assertSessionDoesntHaveErrors();

        $path = OrderItem::query()->firstOrFail()->image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_order_store_validates_lines_and_saves_pdf(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => []])->assertSessionHasErrors('lines');

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => [[
            'item_name' => 'Bolt', 'quantity' => 3,
            'file' => UploadedFile::fake()->create('po.pdf', 10, 'application/pdf'),
        ]]])->assertRedirect('/orders');

        $this->assertNotNull(OrderItem::first()->po_pdf_path);
        Storage::disk('public')->assertExists(OrderItem::first()->po_pdf_path);
    }

    public function test_challan_rejects_duplicate_lines(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => Order::factory()->create(['client_id' => $client->id])->id, 'quantity' => 10]);

        $this->post('/challans', ['client_id' => $client->id, 'issued_on' => '2026-10-01', 'lines' => [
            ['order_item_id' => $item->id, 'quantity' => 1],
            ['order_item_id' => $item->id, 'quantity' => 2],
        ]])->assertSessionHasErrors('lines');
        $this->assertSame(0, DeliveryChallan::count());
    }
}
