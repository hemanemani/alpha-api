<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_number')->unique()->default(56565);
            $table->string('name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('seller_assigned')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('seller_offer_rate', 10, 2)->nullable();
            $table->string('gst')->nullable();
            $table->decimal('buyer_offer_rate', 10, 2)->nullable();
            $table->decimal('final_shipping_value', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->string('buyer_gst_number')->nullable();
            $table->string('buyer_pan')->nullable();
            $table->text('buyer_bank_details')->nullable();

            $table->decimal('amount_received', 12, 2)->nullable();
            $table->date('amount_received_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->date('amount_paid_date')->nullable();

            $table->string('logistics_through')->nullable();
            $table->string('logistics_agency')->nullable();
            $table->decimal('shipping_estimate_value', 12, 2)->nullable();
            $table->decimal('buyer_final_shipping_value', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
