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

        $this->post('/clients', ['name' => '', 'address' => ''])
            ->assertSessionHasErrors('name')->assertSessionDoesntHaveErrors('address');
        $this->put("/clients/{$client->id}", ['name' => 'Acme', 'address' => 'Street 1'])
            ->assertSessionDoesntHaveErrors()->assertRedirect('/clients');
    }

    public function test_blank_client_phone_and_address_are_saved_as_placeholder(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/clients', ['name' => 'Blank Co', 'phone' => '', 'address' => '   '])->assertSessionDoesntHaveErrors();
        $this->post('/clients', ['name' => 'Missing Co'])->assertSessionDoesntHaveErrors();
        $this->post('/clients', ['name' => 'Full Co', 'phone' => '0300-1234567', 'address' => 'Street 1'])->assertSessionDoesntHaveErrors();

        foreach (['Blank Co', 'Missing Co'] as $name) {
            $client = Client::query()->where('name', $name)->firstOrFail();
            $this->assertSame('----------', $client->phone);
            $this->assertSame('----------', $client->address);
        }

        $full = Client::query()->where('name', 'Full Co')->firstOrFail();
        $this->assertSame('0300-1234567', $full->phone);
        $this->assertSame('Street 1', $full->address);
    }

    public function test_client_update_fills_cleared_fields_but_keeps_ones_not_sent(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['name' => 'Acme', 'phone' => '0300-1234567', 'address' => 'Street 1']);

        $this->put("/clients/{$client->id}", ['name' => 'Acme', 'address' => ''])->assertSessionDoesntHaveErrors();

        $client->refresh();
        $this->assertSame('0300-1234567', $client->phone);
        $this->assertSame('----------', $client->address);
    }

    public function test_order_store_saves_line_image_and_rejects_non_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $lines = [['item_name' => 'Bolt', 'quantity' => 5], ['item_name' => 'Nut', 'quantity' => 2]];

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => $lines, 'po_file' => UploadedFile::fake()->create('x.exe', 10, 'application/octet-stream')])
            ->assertSessionHasErrors('po_file');

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => $lines, 'po_file' => UploadedFile::fake()->image('bolt.jpg')])
            ->assertSessionDoesntHaveErrors();

        $paths = OrderItem::query()->pluck('image_path');
        $this->assertCount(2, $paths);
        $this->assertCount(1, $paths->unique(), 'One upload is shared by every line.');
        $this->assertNotNull($paths->first());
        Storage::disk('public')->assertExists($paths->first());
        $this->assertCount(1, Storage::disk('public')->allFiles('po_images'));
    }

    public function test_order_store_without_po_file_leaves_paths_empty(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => [['item_name' => 'Bolt', 'quantity' => 1]]])
            ->assertSessionDoesntHaveErrors();

        $item = OrderItem::query()->firstOrFail();
        $this->assertNull($item->po_pdf_path);
        $this->assertNull($item->image_path);
    }

    public function test_order_store_validates_lines_and_saves_pdf(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => []])->assertSessionHasErrors('lines');

        $this->post('/orders', [
            'client_id' => $client->id, 'po_number' => 'P1',
            'lines' => [['item_name' => 'Bolt', 'quantity' => 3]],
            'po_file' => UploadedFile::fake()->create('po.pdf', 10, 'application/pdf'),
        ])->assertRedirect('/orders');

        $this->assertNotNull(OrderItem::first()->po_pdf_path);
        Storage::disk('public')->assertExists(OrderItem::first()->po_pdf_path);
    }

    public function test_challan_rejects_duplicate_lines(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => Order::factory()->create(['client_id' => $client->id])->id, 'quantity' => 10]);

        $this->post('/challans', ['client_id' => $client->id, 'challan_number' => 'DC-DUP', 'issued_on' => '2026-10-01', 'lines' => [
            ['order_item_id' => $item->id, 'quantity' => 1],
            ['order_item_id' => $item->id, 'quantity' => 2],
        ]])->assertSessionHasErrors('lines');
        $this->assertSame(0, DeliveryChallan::count());
    }
}
