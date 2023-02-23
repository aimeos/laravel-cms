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
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 5);
            $table->string('name', 100);
            $table->string('title');
            $table->string('slug');
            $table->string('to')->nullable();
            $table->string('tag', 30)->nullable();
            $table->json('data');
            $table->smallInteger('status');
            $table->smallInteger('cache')->nullable();
            $table->nestedSet();
            $table->timestamps();
            $table->softDeletes();

            $table->unique( ['slug', 'lang'] );
            $table->unique( ['tag', 'lang', 'status'] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_pages');
    }
};
