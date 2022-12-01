<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebScrapersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_scrapers', function (Blueprint $table) {
            $table->id();
            $table->string('p_title')->nullable();
            $table->string('location')->nullable();
            $table->string('property_type')->nullable();
            $table->string('bedroom')->nullable();
            $table->string('bathroom')->nullable();
            $table->string('deposit')->nullable();
            $table->string('price_p_month')->nullable();
            $table->string('price_p_week')->nullable();
            $table->longText('key_feature')->nullable();
            $table->longText('description')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_address')->nullable();
            $table->string('agent_contact_no')->nullable();
            $table->longText('agent_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_scrapers');
    }
}
