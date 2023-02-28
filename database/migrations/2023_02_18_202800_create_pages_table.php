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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('lang', 5);
            $table->string('name', 100);
            $table->string('title');
            $table->string('slug');
            $table->string('to')->nullable();
            $table->string('tag', 30)->nullable();
            $table->json('data');
            $table->json('config');
            $table->smallInteger('status');
            $table->smallInteger('cache')->nullable();
            $table->nestedSet();
            $table->string('editor');
            $table->timestamps();
            $table->softDeletes();

            $table->unique( ['slug', 'lang', 'tenant_id'] );
            $table->unique( ['tag', 'lang', 'tenant_id', 'status'] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_pages');
    }
};
