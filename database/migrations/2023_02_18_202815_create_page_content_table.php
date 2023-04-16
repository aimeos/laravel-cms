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
            $table->smallInteger('status');
            $table->integer('position');
            $table->boolean('published');
            $table->string('editor');
            $table->datetime('start')->nullable();
            $table->datetime('end')->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->unique(['page_id', 'content_id', 'published', 'tenant_id'], 'unq_pid_cid_pub_tid');
            $table->index(['page_id', 'content_id', 'status', 'start', 'end', 'position'], 'idx_pid_cid_status_start_end_pos');
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
