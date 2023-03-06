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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_content_file', function (Blueprint $table) {
            $table->foreignUuid('content_id')->constrained('cms_contents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('file_id')->constrained('cms_pages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tenant_id');

            $table->unique(['content_id', 'file_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_content_file');
    }
};
