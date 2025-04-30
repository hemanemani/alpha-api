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
        Schema::table('international_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('international_offer_id')->after('id');

            $table->foreign('international_offer_id')
                  ->references('id')
                  ->on('international_offers')
                  ->onDelete('cascade'); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('international_orders', function (Blueprint $table) {
            //
        });
    }
};
