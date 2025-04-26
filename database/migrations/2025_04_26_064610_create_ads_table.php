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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('ad_title');
            $table->string('type')->nullable();
            $table->dateTime('date_published')->nullable();
            $table->string('platform')->nullable();
            $table->string('status')->nullable();
            $table->string('goal')->nullable();
            $table->string('audience')->nullable();
            $table->decimal('budget_set', 10, 2);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('messages_received')->default(0);
            $table->decimal('cost_per_message', 10, 2)->default(0);
            $table->string('top_location')->nullable();
            $table->unsignedBigInteger('post_reactions')->default(0);
            $table->unsignedBigInteger('post_shares')->default(0);
            $table->unsignedBigInteger('post_save')->default(0);
            $table->decimal('total_amount_spend', 12, 2)->default(0);
            $table->string('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
