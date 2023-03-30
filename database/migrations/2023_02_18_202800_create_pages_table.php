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
            $table->string('tenant_id', 250);
            $table->string('lang', 5)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('to')->nullable();
            $table->string('domain')->nullable();
            $table->string('tag', 30)->nullable();
            $table->json('data');
            $table->json('config');
            $table->smallInteger('status');
            $table->smallInteger('cache');
            $table->nestedSet();
            $table->string('editor');
            $table->datetime('start')->nullable();
            $table->datetime('end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'domain', 'lang', 'tenant_id']);
            $table->index(['_lft', '_rgt', 'tenant_id', 'status']);
            $table->index(['tag', 'lang', 'tenant_id', 'status', 'start', 'end']);
            $table->index(['lang', 'tenant_id', 'status', 'start', 'end']);
            $table->index(['parent_id', 'tenant_id']);
            $table->index(['deleted_at']);
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
