<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class StockFilters
{
    public const STATUSES = ['pending', 'completed', 'all'];

    public function __construct(
        public readonly ?int $clientId = null,
        public readonly bool $unassigned = false,
        public readonly ?int $orderId = null,
        public readonly ?string $poNumber = null,
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
        public readonly string $status = 'pending',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $client = $request->query('client_id');

        return new self(
            clientId: is_numeric($client) ? (int) $client : null,
            unassigned: $client === 'unassigned',
            orderId: is_numeric($request->query('order_id')) ? (int) $request->query('order_id') : null,
            poNumber: self::clean($request->query('po_number')),
            from: self::date($request->query('from')),
            to: self::date($request->query('to')),
            status: in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : 'pending',
        );
    }

    /** Query-string form, used to carry filters into PDF links. */
    public function toQuery(): array
    {
        return array_filter([
            'client_id' => $this->unassigned ? 'unassigned' : $this->clientId,
            'order_id' => $this->orderId,
            'po_number' => $this->poNumber,
            'from' => $this->from?->toDateString(),
            'to' => $this->to?->toDateString(),
            'status' => $this->status !== 'pending' ? $this->status : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** Human-readable summary for PDF headers. */
    public function describe(?string $clientName = null): string
    {
        $parts = [];
        if ($this->unassigned) {
            $parts[] = 'Client: Unassigned';
        } elseif ($this->clientId) {
            $parts[] = 'Client: '.($clientName ?? '#'.$this->clientId);
        }
        if ($this->orderId) {
            $parts[] = 'Order #'.$this->orderId;
        }
        if ($this->poNumber) {
            $parts[] = 'PO: '.$this->poNumber;
        }
        if ($this->from) {
            $parts[] = 'From '.$this->from->toDateString();
        }
        if ($this->to) {
            $parts[] = 'To '.$this->to->toDateString();
        }
        $parts[] = 'Status: '.ucfirst($this->status);

        return implode(' · ', $parts);
    }

    private static function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    private static function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
