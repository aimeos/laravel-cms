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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_file_element', function (Blueprint $table) {
            $table->foreignUuid('file_id')->constrained('cms_files')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('element_id')->constrained('cms_elements')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['file_id', 'element_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_file_element');
    }
};
