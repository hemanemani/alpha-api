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
        Schema::create('international_ads', function (Blueprint $table) {
            $table->id();
            $table->string('ad_title');
            $table->string('type')->nullable();
            $table->dateTime('date_published')->nullable();
            $table->string('platform')->nullable();
            $table->string('status')->nullable();
            $table->string('goal')->nullable();
            $table->string('audience')->nullable();
            $table->decimal('budget_set', 10, 2)->nullable();
            $table->unsignedBigInteger('views')->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('messages_received')->nullable();
            $table->decimal('cost_per_message', 10, 2)->nullable();
            $table->string('top_location')->nullable();
            $table->unsignedBigInteger('post_reactions')->nullable();
            $table->unsignedBigInteger('post_shares')->nullable();
            $table->unsignedBigInteger('post_save')->nullable();
            $table->decimal('total_amount_spend', 12, 2)->nullable();
            $table->string('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('international_ads');
    }
};
