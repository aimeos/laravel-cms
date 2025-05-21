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
            $table->string('name');
            $table->string('slug');
            $table->string('to')->nullable();
            $table->string('title')->nullable();
            $table->string('domain')->nullable();
            $table->string('lang', 5)->nullable();
            $table->string('tag', 30)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('theme', 30)->nullable();
            $table->smallInteger('cache');
            $table->smallInteger('status');
            $table->json('meta')->nullable();
            $table->json('config')->nullable();
            $table->json('contents')->nullable();
            $table->string('editor');
            $table->softDeletes();
            $table->timestamps();
            $table->nestedSet();

            $table->unique(['slug', 'domain', 'lang', 'tenant_id']);
            $table->index(['_lft', '_rgt', 'tenant_id', 'status']);
            $table->index(['tag', 'lang', 'tenant_id', 'status']);
            $table->index(['lang', 'tenant_id', 'status']);
            $table->index(['parent_id', 'tenant_id']);
            $table->index(['domain', 'tenant_id']);
            $table->index(['to', 'tenant_id']);
            $table->index(['name', 'tenant_id']);
            $table->index(['title', 'tenant_id']);
            $table->index(['type', 'tenant_id']);
            $table->index(['theme', 'tenant_id']);
            $table->index(['cache', 'tenant_id']);
            $table->index(['editor', 'tenant_id']);
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
