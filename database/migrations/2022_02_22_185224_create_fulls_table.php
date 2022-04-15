<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fulls', function (Blueprint $table) {
            $table->id();
            $table->string('stock');
            $table->integer('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('price');
            $table->integer('show_price');
            
            $table->unsignedBigInteger('waranty_id');
            $table->foreign('waranty_id')->references('id')->on('waranties')->onUpdate('cascade');
            
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade');
            
            $table->unsignedBigInteger('color_id')->nullable();
            $table->foreign('color_id')->references('id')->on('colors')->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['product_id', 'color_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fulls');
    }
};
