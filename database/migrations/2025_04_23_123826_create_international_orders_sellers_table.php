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
        Schema::create('international_orders_sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('international_order_id')->constrained()->onDelete('cascade');
            $table->string('seller_name')->nullable();
            $table->string('seller_address')->nullable();
            $table->string('seller_contact')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('seller_pincode')->nullable();
            $table->string('seller_contact_person_name')->nullable();
            $table->string('seller_contact_person_number')->nullable();
            $table->integer('no_of_boxes')->nullable();
            $table->decimal('weight_per_unit', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('dimension_unit')->nullable();
            $table->date('invoice_generate_date')->nullable();
            $table->decimal('invoice_value', 10, 2)->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('order_ready_date')->nullable();
            $table->date('invoicing_invoice_generate_date')->nullable();
            $table->string('invoicing_invoice_number')->nullable();
            $table->string('invoice_to')->nullable();
            $table->text('invoice_address')->nullable();
            $table->string('invoice_gstin')->nullable();
            $table->decimal('packaging_expenses', 10, 2)->default(0);
            $table->decimal('invoicing_total_amount', 10, 2)->default(0);
            $table->string('total_amount_in_words')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('rate_per_kg', 10, 2)->default(0);
            $table->decimal('total_kg', 10, 2)->default(0);
            $table->string('hsn')->nullable();
            $table->decimal('invoicing_amount', 10, 2)->default(0);
            $table->decimal('expenses', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('international_orders_sellers');
    }
};
