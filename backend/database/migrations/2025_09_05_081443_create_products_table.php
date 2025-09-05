<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name');
            $table->string('product_type');
            $table->unsignedBigInteger('product_parent_id')->nullable();
            $table->timestamps();

            $table->foreign('product_parent_id')
                  ->references('product_id')
                  ->on('products')
                  ->onDelete('cascade');
            
            $table->index(['product_parent_id']);
            $table->index(['product_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
