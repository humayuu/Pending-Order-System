<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanLine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\OrderClientBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiClientTest extends TestCase
{
    use RefreshDatabase;

    private function item(Client $client, string $po, int $qty = 10, string $name = 'Bolt'): OrderItem
    {
        $order = Order::factory()->create(['client_id' => $client->id]);

        return OrderItem::factory()->create(['order_id' => $order->id, 'po_number' => $po, 'item_name' => $name, 'quantity' => $qty]);
    }

    private function deliver(Client $client, OrderItem $item, int $qty): DeliveryChallan
    {
        $ch = DeliveryChallan::factory()->create(['client_id' => $client->id]);
        DeliveryChallanLine::create(['delivery_challan_id' => $ch->id, 'order_item_id' => $item->id, 'quantity' => $qty]);

        return $ch;
    }

    private function login(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_order_requires_valid_client_and_saves_it(): void
    {
        $client = Client::factory()->create();
        $line = ['item_name' => 'Bolt', 'quantity' => 5];

        $this->login()->post('/orders', ['po_number' => 'P1', 'lines' => [$line]])->assertSessionHasErrors('client_id');
        $this->post('/orders', ['client_id' => 999, 'po_number' => 'P1', 'lines' => [$line]])->assertSessionHasErrors('client_id');
        $this->post('/orders', ['client_id' => $client->id, 'po_number' => 'P1', 'lines' => [$line]])->assertRedirect('/orders');

        $this->assertSame($client->id, Order::first()->client_id);
    }

    public function test_order_client_change_blocked_when_other_client_challan_exists(): void
    {
        $a = Client::factory()->create();
        $b = Client::factory()->create();
        $item = $this->item($a, 'P1');
        $order = $item->order;

        $this->login()->put("/orders/{$order->id}", ['client_id' => $b->id, 'reference' => 'R'])->assertRedirect();
        $this->assertSame($b->id, $order->fresh()->client_id);
        $this->assertSame('R', $order->fresh()->reference);

        $this->deliver($b, $item, 2);
        $this->put("/orders/{$order->id}", ['client_id' => $a->id])->assertSessionHasErrors('client_id');
        $this->assertSame($b->id, $order->fresh()->client_id);
    }

    public function test_challan_rejects_other_clients_and_unassigned_lines(): void
    {
        $a = Client::factory()->create();
        $b = Client::factory()->create();
        $bItem = $this->item($b, 'PB');
        $orphan = OrderItem::factory()->create(['order_id' => Order::factory()->unassigned()->create()->id, 'quantity' => 5]);
        $aItem = $this->item($a, 'PA');

        $payload = fn (OrderItem $i, string $dc = 'DC-1') => ['client_id' => $a->id, 'challan_number' => $dc, 'issued_on' => '2026-10-01', 'lines' => [['order_item_id' => $i->id, 'quantity' => 1]]];

        $this->login()->post('/challans', $payload($bItem))->assertSessionHasErrors('lines');
        $this->post('/challans', $payload($orphan))->assertSessionHasErrors('lines');
        $this->assertSame(0, DeliveryChallan::count());

        $this->post('/challans', $payload($aItem))->assertRedirect('/challans');
        $this->assertSame(1, DeliveryChallan::count());

        $this->post('/challans', ['lines' => [['order_item_id' => $aItem->id, 'quantity' => 99]]] + $payload($aItem, 'DC-2'))->assertSessionHasErrors('lines');
    }

    public function test_challan_number_is_manual_required_and_unique(): void
    {
        $a = Client::factory()->create();
        $item = $this->item($a, 'PA');
        $payload = fn (?string $dc) => ['client_id' => $a->id, 'challan_number' => $dc, 'issued_on' => '2026-10-01', 'lines' => [['order_item_id' => $item->id, 'quantity' => 1]]];

        $this->login()->post('/challans', $payload(null))->assertSessionHasErrors('challan_number');
        $this->post('/challans', $payload(' DC-100 '))->assertRedirect('/challans');
        $this->assertSame('DC-100', DeliveryChallan::first()->challan_number);

        foreach (['DC-100', ' DC-100 ', 'dc-100'] as $dup) {
            $this->post('/challans', $payload($dup))->assertSessionHasErrors('challan_number');
        }
        $this->assertSame(1, DeliveryChallan::count());
    }

    public function test_create_form_starts_with_empty_dc_number_and_quantity(): void
    {
        DeliveryChallan::factory()->create(['client_id' => Client::factory()->create()->id, 'challan_number' => 'DC-2026-0001']);

        $html = $this->login()->get('/challans/create')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="challan_number"[^>]*value=""/', $html);
        $this->assertStringNotContainsString('DC-2026-0002', $html);
        $this->assertDoesNotMatchRegularExpression('/qty-input"[^>]*value=/', $html);
    }

    public function test_order_show_lists_deliveries_per_po_line(): void
    {
        $a = Client::factory()->create();
        $item = $this->item($a, 'PO-55', 10, 'Washer');
        $idle = OrderItem::factory()->create(['order_id' => $item->order_id, 'po_number' => 'PO-56', 'quantity' => 4]);

        $first = DeliveryChallan::factory()->create(['client_id' => $a->id, 'challan_number' => 'DC-A1', 'issued_on' => '2026-10-01']);
        $second = DeliveryChallan::factory()->create(['client_id' => $a->id, 'challan_number' => 'DC-B2', 'issued_on' => '2026-10-05']);
        DeliveryChallanLine::create(['delivery_challan_id' => $second->id, 'order_item_id' => $item->id, 'quantity' => 2]);
        DeliveryChallanLine::create(['delivery_challan_id' => $first->id, 'order_item_id' => $item->id, 'quantity' => 3]);

        $this->login()->get('/orders/'.$item->order_id)
            ->assertOk()
            ->assertSeeInOrder(['PO-55', 'DC-A1', '3', 'DC-B2', '2', 'Delivered 5 of 10', 'Pending 5', 'PO-56', 'No deliveries yet.'])
            ->assertSee(route('challans.show', $first))
            ->assertSee('PO: PO-55, PO-56')
            ->assertDontSee('Order #');
    }

    public function test_challan_create_payload_is_client_tagged_and_skips_unassigned(): void
    {
        $a = Client::factory()->create();
        $aItem = $this->item($a, 'PA');
        OrderItem::factory()->create(['order_id' => Order::factory()->unassigned()->create()->id]);

        $response = $this->login()->get('/challans/create')->assertOk();
        $lines = $response->viewData('availableLines');

        $this->assertCount(1, $lines);
        $this->assertSame($a->id, $lines[0]['client_id']);
    }

    public function test_same_po_number_for_two_clients_stays_separate_in_reports(): void
    {
        $a = Client::factory()->create(['name' => 'Alpha']);
        $b = Client::factory()->create(['name' => 'Beta']);
        $this->item($a, 'PO-1', 10);
        $this->item($b, 'PO-1', 7);

        $this->login()->get('/reports/item-and-po-wise')->assertOk()->assertViewHas('blocks', fn ($b) => $b->count() === 2);
        $this->get('/reports/item-and-po-wise?client_id='.$b->id)->assertViewHas('blocks', fn ($b) => $b->count() === 1 && $b->first()->items->first()->total === 7);
    }

    public function test_report_filters_status_date_order_and_po(): void
    {
        $a = Client::factory()->create();
        $done = $this->item($a, 'DONE', 5, 'Nut');
        $open = $this->item($a, 'OPEN', 5, 'Screw');
        $this->deliver($a, $done, 5);
        $done->order->forceFill(['created_at' => '2026-01-10 12:00:00'])->save();
        $open->order->forceFill(['created_at' => '2026-03-10 12:00:00'])->save();

        $rows = fn (string $qs) => $this->login()->get('/reports/item-and-po-wise'.$qs)->viewData('blocks')->flatMap(fn ($b) => $b->items->flatMap(fn ($i) => $i->cells->keys()))->unique()->values();

        $this->assertSame(['OPEN'], $rows('')->all());
        $this->assertSame(['DONE'], $rows('?status=completed')->all());
        $this->assertCount(2, $rows('?status=all'));
        $this->assertSame(['OPEN'], $rows('?status=all&from=2026-03-01')->all());
        $this->assertSame(['DONE'], $rows('?status=all&to=2026-01-10')->all());
        $this->assertSame(['OPEN'], $rows('?status=all&po_number=OPE')->all());
        $this->assertSame(['DONE'], $rows('?status=all&order_id='.$done->order_id)->all());
        $this->assertCount(2, $rows('?status=bogus&from=garbage&client_id=abc&status=all'));
    }

    public function test_client_summary_and_dashboard_totals(): void
    {
        $a = Client::factory()->create(['name' => 'Alpha']);
        $b = Client::factory()->create(['name' => 'Beta']);
        Client::factory()->create(['name' => 'Empty']);
        $i1 = $this->item($a, 'P1', 10);
        $this->item($a, 'P2', 4);
        $this->item($b, 'P3', 6);
        $this->deliver($a, $i1, 10);
        OrderItem::factory()->create(['order_id' => Order::factory()->unassigned()->create()->id, 'quantity' => 3]);

        $dash = $this->login()->get('/dashboard')->assertOk();
        $summary = $dash->viewData('clientSummary')->keyBy('client_name');

        $this->assertSame(4, $summary['Alpha']->pending_qty);
        $this->assertSame(2, $summary['Alpha']->orders);
        $this->assertSame(1, $summary['Alpha']->open_orders);
        $this->assertSame(6, $summary['Beta']->pending_qty);
        $this->assertSame(0, $summary['Empty']->pending_qty);
        $this->assertSame(3, $summary['Unassigned']->pending_qty);
        $this->assertSame(13, $dash->viewData('overall')->pending_qty);
        $this->assertSame(1, $dash->viewData('unassignedOrders'));

        $this->get('/dashboard?client_id='.$b->id)->assertViewHas('pendingLines', fn ($l) => $l->count() === 1);
    }

    public function test_pdfs_download_with_filters(): void
    {
        $a = Client::factory()->create();
        $this->item($a, 'P1');
        $this->login();

        foreach (['item-and-po-wise'] as $r) {
            $this->get("/reports/{$r}/pdf?client_id={$a->id}&status=all")->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_item_and_po_wise_pdf_is_named_after_client_and_status(): void
    {
        $paul = Client::factory()->create(['name' => 'Paul Brooks']);
        $odd = Client::factory()->create(['name' => 'A/B: Co']);
        $this->item($paul, 'P1');
        $this->login();

        $name = fn (string $qs) => $this->get('/reports/item-and-po-wise/pdf'.$qs)->assertOk()->headers->get('content-disposition');

        $this->assertStringContainsString('filename="Paul Brooks-Pending.pdf"', $name('?client_id='.$paul->id));
        $this->assertStringContainsString('filename="Paul Brooks-Completed.pdf"', $name('?client_id='.$paul->id.'&status=completed'));
        $this->assertStringContainsString('filename="All Clients-Pending.pdf"', $name(''));
        $this->assertStringContainsString('filename="A B Co-Pending.pdf"', $name('?client_id='.$odd->id));
    }

    public function test_order_report_pdf_has_one_button_and_downloads(): void
    {
        $a = Client::factory()->create();
        $item = $this->item($a, 'PO-55');
        OrderItem::factory()->create(['order_id' => $item->order_id, 'po_number' => 'PO-55']);
        $this->deliver($a, $item, 4);
        $this->login();

        $url = route('orders.po-report', $item->order_id);
        $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame(1, substr_count($this->get('/orders/'.$item->order_id)->getContent(), $url));

        $this->get(route('orders.po-report', Order::factory()->create(['client_id' => $a->id])))->assertNotFound();
    }

    public function test_client_delete_blocked_when_in_use(): void
    {
        $withOrder = Client::factory()->create();
        $this->item($withOrder, 'P1');
        $withChallan = Client::factory()->create();
        $this->deliver($withChallan, $this->item($withOrder, 'P2'), 1);
        $unused = Client::factory()->create();

        $this->login()->delete("/clients/{$withOrder->id}")->assertSessionHasErrors('client');
        $this->delete("/clients/{$withChallan->id}")->assertSessionHasErrors('client');
        $this->delete("/clients/{$unused->id}")->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', ['id' => $withOrder->id]);
        $this->assertDatabaseHas('clients', ['id' => $withChallan->id]);
        $this->assertDatabaseMissing('clients', ['id' => $unused->id]);
    }

    public function test_backfill_assigns_only_unambiguous_orders_and_is_idempotent(): void
    {
        $a = Client::factory()->create();
        $b = Client::factory()->create();
        $single = $this->item($a, 'S');
        $mixed = $this->item($a, 'M');
        $none = $this->item($a, 'N');
        foreach ([$single, $mixed, $none] as $i) {
            $i->order->update(['client_id' => null]);
        }
        $this->deliver($a, $single, 1);
        $this->deliver($a, $mixed, 1);
        $this->deliver($b, $mixed, 1);

        $this->assertSame(1, OrderClientBackfill::run());
        $this->assertSame($a->id, $single->order->fresh()->client_id);
        $this->assertNull($mixed->order->fresh()->client_id);
        $this->assertNull($none->order->fresh()->client_id);
        $this->assertSame(0, OrderClientBackfill::run());
    }

    public function test_list_pages_render_and_filter(): void
    {
        $a = Client::factory()->create();
        $b = Client::factory()->create();
        $this->item($a, 'P1');
        $this->item($b, 'P2');
        Order::factory()->unassigned()->create();
        $this->login();

        $this->get('/orders')->assertOk()->assertSee("new TomSelect('#f_client'", false);
        $this->get('/challans')->assertOk()->assertSee("new TomSelect('#f_client'", false);
        $this->get('/challans/create')->assertOk()->assertSee('new TomSelect(clientSelect', false);
        $this->get('/reports/item-and-po-wise')->assertOk()->assertSee("new TomSelect('#f_client'", false);
        $this->get('/orders?client_id='.$a->id)->assertViewHas('orders', fn ($o) => $o->total() === 1);
        $this->get('/orders?client_id=unassigned')->assertViewHas('orders', fn ($o) => $o->total() === 1);
        $this->get('/orders/create')->assertOk();
        $this->get('/orders/'.Order::first()->id.'/edit')->assertOk();
        $this->get('/clients')->assertOk();
        $this->get('/challans?client_id='.$a->id.'&from=2026-01-01')->assertOk();
        $this->get('/reports')->assertRedirect(route('reports.item-and-po-wise'));
        $this->get('/reports/item-and-po-wise')->assertOk();
    }

    public function test_orders_list_shows_distinct_po_numbers_instead_of_id(): void
    {
        $client = Client::factory()->create();
        $item = $this->item($client, 'PO-777');
        OrderItem::factory()->create(['order_id' => $item->order_id, 'po_number' => 'PO-777']);
        OrderItem::factory()->create(['order_id' => $item->order_id, 'po_number' => 'PO-888']);
        Order::factory()->create(['client_id' => $client->id]);

        $this->login()->get('/orders')
            ->assertOk()
            ->assertSee('PO number')
            ->assertSee('PO-777, PO-888')
            ->assertSee('—')
            ->assertDontSee('#'.$item->order_id.'<', false);
    }

    public function test_client_name_must_be_unique(): void
    {
        $this->login();
        $a = Client::factory()->create(['name' => 'Acme']);
        $b = Client::factory()->create(['name' => 'Beta']);

        $this->post('/clients', ['name' => 'Acme', 'address' => 'x'])->assertSessionHasErrors('name');
        $this->put("/clients/{$b->id}", ['name' => 'Acme', 'address' => 'x'])->assertSessionHasErrors('name');
        $this->put("/clients/{$a->id}", ['name' => 'Acme', 'address' => 'y'])->assertSessionHasNoErrors();
    }
}
