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
        Schema::create('international_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_number')->unique();
            $table->string('mobile_number');
            $table->date('inquiry_date');
            $table->string('product_categories');
            $table->string('specific_product');
            $table->string('name'); 
            $table->string('location');
            $table->string('inquiry_through');
            $table->string('inquiry_reference');
            $table->date('first_contact_date');
            $table->text('first_response');
            $table->date('second_contact_date')->nullable();
            $table->text('second_response')->nullable();
            $table->date('third_contact_date')->nullable();
            $table->text('third_response')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('international_inquiries');
    }
};
