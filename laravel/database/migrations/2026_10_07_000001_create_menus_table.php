<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->integer('price');
            $table->integer('calories')->nullable(); // based on UI design
            $table->boolean('is_healthy')->default(false); // UI shows healthy tag
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->string('merchant_name')->nullable(); // UI shows "Stand Bu Rita", etc
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
