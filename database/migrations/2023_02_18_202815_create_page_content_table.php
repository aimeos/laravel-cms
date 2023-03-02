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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_page_content', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignId('page_id')->constrained('cms_pages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('content_id')->constrained('cms_contents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tenant_id');
            $table->integer('position');
            $table->smallInteger('status');
            $table->string('editor');
            $table->timestamps();

            $table->primary('id');
            $table->unique(['page_id', 'content_id', 'tenant_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_page_content');
    }
};
