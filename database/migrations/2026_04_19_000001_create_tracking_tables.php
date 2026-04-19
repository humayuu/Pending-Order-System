<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->string('po_number');
            $table->unsignedInteger('quantity');
            $table->string('po_pdf_path')->nullable();
            $table->timestamps();

            $table->index(['po_number', 'item_name']);
        });

        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('challan_number')->unique();
            $table->date('issued_on');
            $table->string('vehicle_no')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_challan_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_challan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['delivery_challan_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_challan_lines');
        Schema::dropIfExists('delivery_challans');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('clients');
    }
};
