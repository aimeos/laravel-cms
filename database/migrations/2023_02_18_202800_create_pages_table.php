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
            $table->string('title');
            $table->string('slug');
            $table->string('to')->nullable();
            $table->string('tag', 31)->nullable();
            $table->json('data');
            $table->smallInteger('status');
            $table->nestedSet();
            $table->timestamps();
            $table->softDeletes();

            $table->unique( ['slug', 'lang'] );
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
