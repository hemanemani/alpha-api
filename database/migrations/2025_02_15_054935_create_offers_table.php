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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_number');
            $table->date('communication_date');
            $table->unsignedInteger('received_sample_amount');
            $table->unsignedInteger('sent_sample_amount');
            $table->date('sample_dispatched_date');
            $table->string('sample_sent_through');
            $table->date('sample_received_date');
            $table->text('offer_notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
