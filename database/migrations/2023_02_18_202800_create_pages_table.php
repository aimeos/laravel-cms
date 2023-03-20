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
            $table->string('lang', 5);
            $table->string('name', 100);
            $table->string('title');
            $table->string('slug');
            $table->string('to');
            $table->string('domain');
            $table->string('tag', 30);
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
